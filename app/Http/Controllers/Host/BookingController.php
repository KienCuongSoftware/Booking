<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTransaction;
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
    ) {}

    public function index(Request $request): View
    {
        $query = Booking::query()
            ->whereHas('hotel', function ($builder) use ($request): void {
                $builder->where('host_id', $request->user()->id);
            })
            ->with(['customer:id,name,email', 'hotel:id,name', 'roomType:id,name', 'transactions.actor:id,name']);

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

        $bookings = $query->latest('id')->paginate(12)->withQueryString();

        return view('host.bookings.index', compact('bookings'));
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
