<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Hotel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function cancellable(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->whereDate('check_in_date', '>', now()->addDay()->toDateString())
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(10);

        return view('customer.bookings.cancellable', compact('bookings'));
    }

    public function rebook(Request $request): View
    {
        $bookings = $request->user()
            ->bookings()
            ->whereIn('status', [BookingStatus::Cancelled->value, BookingStatus::Completed->value])
            ->with(['hotel:id,name,slug', 'roomType:id,name'])
            ->latest('id')
            ->paginate(10);

        return view('customer.bookings.rebook', compact('bookings'));
    }

    public function store(Request $request, Hotel $hotel): RedirectResponse
    {
        $validated = $request->validate([
            'room_type_id' => ['required', 'integer'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'payment_method' => ['required', 'in:cash,bank_transfer'],
            'payment_provider' => ['nullable', 'in:momo,paypal'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $roomType = $hotel->roomTypes()
            ->where('is_active', true)
            ->where('id', $validated['room_type_id'])
            ->firstOrFail();

        if ((int) $validated['guest_count'] > $roomType->max_occupancy) {
            return back()->withErrors([
                'guest_count' => __('Số khách vượt quá sức chứa của loại phòng đã chọn.'),
            ])->withInput();
        }

        $checkIn = \Illuminate\Support\Carbon::parse($validated['check_in_date']);
        $checkOut = \Illuminate\Support\Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);
        $unitPrice = (float) ($roomType->new_price ?? $roomType->base_price);
        $totalPrice = $unitPrice * $nights;

        $paymentMethod = BookingPaymentMethod::from($validated['payment_method']);
        $paymentProvider = $paymentMethod === BookingPaymentMethod::BankTransfer
            ? BookingPaymentProvider::from($validated['payment_provider'] ?? 'momo')
            : null;
        $paymentStatus = $paymentMethod === BookingPaymentMethod::Cash
            ? BookingPaymentStatus::Unpaid
            : BookingPaymentStatus::Pending;

        Booking::query()->create([
            'booking_code' => 'BK'.strtoupper(Str::random(8)),
            'customer_id' => $request->user()->id,
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'guest_count' => (int) $validated['guest_count'],
            'nights' => $nights,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'status' => BookingStatus::Pending,
            'payment_method' => $paymentMethod,
            'payment_provider' => $paymentProvider,
            'payment_status' => $paymentStatus,
            'payment_reference' => $validated['payment_reference'] ?: null,
            'customer_note' => $validated['customer_note'] ?: null,
        ]);

        return redirect()
            ->route('customer.bookings.index')
            ->with('status', __('Đặt phòng thành công. Chủ khách sạn sẽ xử lý đơn của bạn sớm.'));
    }
}

