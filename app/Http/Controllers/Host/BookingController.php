<?php

namespace App\Http\Controllers\Host;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransaction;
use App\Models\PhysicalRoom;
use App\Services\AuditLogService;
use App\Services\BookingLedgerService;
use App\Services\BookingStatusUpdateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly BookingStatusUpdateService $bookingStatusUpdateService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->whereHas('hotel', function ($builder) use ($request): void {
                $builder->where('host_id', $request->user()->id);
            })
            ->with([
                'customer:id,name,email',
                'hotel:id,name',
                'roomType:id,name',
                'roomType.physicalRooms' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
                'transactions.actor:id,name',
            ]);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->value());
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->string('search')->value());

            if ($term !== '') {
                $query->where(function ($q) use ($term): void {
                    $q->where('booking_code', 'like', "%{$term}%")
                        ->orWhereHas('customer', function ($qc) use ($term): void {
                            $qc->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        })
                        ->orWhereHas('hotel', function ($qh) use ($term): void {
                            $qh->where('name', 'like', "%{$term}%");
                        })
                        ->orWhereHas('roomType', function ($qr) use ($term): void {
                            $qr->where('name', 'like', "%{$term}%");
                        });
                });
            }
        }

        $bookings = $query
            ->with(['physicalRoom:id,label'])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('host.bookings.index', compact('bookings'));
    }

    public function updatePhysicalRoom(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'physical_room_id' => ['nullable', 'integer', 'exists:physical_rooms,id'],
        ]);

        $pid = $validated['physical_room_id'] ?? null;
        if ($pid !== null) {
            $exists = PhysicalRoom::query()
                ->where('id', $pid)
                ->where('room_type_id', $booking->room_type_id)
                ->where('is_active', true)
                ->exists();
            if (! $exists) {
                return back()->withErrors(['physical_room_id' => __('Phòng không thuộc loại phòng của đơn hoặc đang tắt.')]);
            }
            if ($this->physicalRoomHasOverlap($booking, (int) $pid)) {
                return back()->withErrors(['physical_room_id' => __('Phòng vật lý đã có đơn trùng khoảng ngày.')]);
            }
        }

        $before = $booking->physical_room_id;
        $booking->physical_room_id = $pid;
        $booking->save();

        $this->auditLogService->record($booking, 'host_physical_room_assigned', $request->user(), [
            'from_physical_room_id' => $before,
            'to_physical_room_id' => $pid,
        ], $request);

        return back()->with('status', __('Đã cập nhật gán phòng vật lý.'));
    }

    private function physicalRoomHasOverlap(Booking $booking, int $physicalRoomId): bool
    {
        return Booking::query()
            ->where('physical_room_id', $physicalRoomId)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', [
                BookingStatus::Pending->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Completed->value,
            ])
            ->whereDate('check_in_date', '<', $booking->check_out_date->toDateString())
            ->whereDate('check_out_date', '>', $booking->check_in_date->toDateString())
            ->exists();
    }

    public function updateStatus(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,cancelled,completed,no_show'],
            'host_note' => ['nullable', 'string', 'max:1000'],
            'internal_tags' => ['nullable', 'string', 'max:500'],
            'mark_paid' => ['nullable', 'boolean'],
        ]);

        try {
            $this->bookingStatusUpdateService->apply($booking, $request->user(), $validated, $request, 'host_status_updated');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Đã cập nhật trạng thái đơn đặt.'));
    }

    public function updateRefundStatus(Request $request, Booking $booking, BookingTransaction $transaction): RedirectResponse
    {
        abort_unless($booking->hotel->host_id === $request->user()->id, 403);
        abort_unless($transaction->booking_id === $booking->id && $transaction->type === 'refund', 404);

        $validated = $request->validate([
            'status' => ['required', 'in:processing,refunded,failed'],
        ]);

        $this->bookingLedgerService->updateRefundStatus(
            $transaction,
            $validated['status'],
            $request->user(),
        );

        return back()->with('status', __('Đã cập nhật trạng thái hoàn tiền.'));
    }
}
