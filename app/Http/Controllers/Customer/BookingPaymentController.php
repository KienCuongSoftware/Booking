<?php

namespace App\Http\Controllers\Customer;

use App\Enums\BookingPaymentStatus;
use App\Enums\BookingPaymentProvider;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingLedgerService;
use App\Services\BookingNotificationService;
use App\Services\MoMoCheckoutService;
use App\Services\PayPalCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class BookingPaymentController extends Controller
{
    public function __construct(
        private readonly PayPalCheckoutService $payPalCheckoutService,
        private readonly MoMoCheckoutService $moMoCheckoutService,
        private readonly BookingLedgerService $bookingLedgerService,
        private readonly BookingNotificationService $bookingNotificationService,
    ) {}

    public function paypalReturn(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string', 'max:191'],
        ]);

        $orderId = $request->string('token')->value();

        $booking = Booking::query()
            ->where('paypal_order_id', $orderId)
            ->where('customer_id', $request->user()->id)
            ->first();

        if (! $booking) {
            return redirect()
                ->route('customer.bookings.index')
                ->with('status', __('Không tìm thấy đơn thanh toán tương ứng.'));
        }

        if ($booking->payment_status === BookingPaymentStatus::Paid) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Thanh toán đã được ghi nhận trước đó.'));
        }

        try {
            $capture = $this->payPalCheckoutService->captureOrder($orderId);
            if (($capture['status'] ?? '') !== 'COMPLETED') {
                return redirect()
                    ->route('customer.bookings.show', $booking)
                    ->withErrors(['paypal' => __('Trạng thái thanh toán PayPal: :s', ['s' => (string) ($capture['status'] ?? '')])]);
            }

            $captureId = (string) ($capture['id'] ?? '');
            $booking->forceFill([
                'payment_status' => BookingPaymentStatus::Paid,
                'paypal_capture_id' => $captureId,
                'payment_reference' => $captureId,
            ])->save();

            $this->bookingLedgerService->recordMarkedPaid($booking, $request->user());
            $this->bookingNotificationService->sendCreated($booking);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['paypal' => __('Không thể hoàn tất thanh toán PayPal. Vui lòng thử lại hoặc liên hệ hỗ trợ.')]);
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->with('status', __('Thanh toán PayPal thành công.'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        return redirect()
            ->route('public.hotels.show', $booking->hotel)
            ->with('status', __('Bạn đã hủy thanh toán. Đơn :code vẫn có thể bị hủy nếu hết thời giữ chỗ.', ['code' => $booking->booking_code]));
    }

    public function paypalResume(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        if (! $booking->isPayPalCheckoutPending()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Đơn này không còn ở trạng thái chờ thanh toán PayPal.'));
        }

        if ($booking->hold_expires_at && $booking->hold_expires_at->isPast()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['paypal' => __('Thời giữ chỗ đã hết. Vui lòng tạo đơn mới nếu cần.')]);
        }

        if (! (string) config('services.paypal.client_id')
            || ! (string) config('services.paypal.client_secret')) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['paypal' => __('Thanh toán PayPal chưa cấu hình.')]);
        }

        try {
            $approvalUrl = $this->payPalCheckoutService->createCheckoutApprovalUrl($booking);
            if (! $approvalUrl) {
                return redirect()
                    ->route('customer.bookings.show', $booking)
                    ->withErrors(['paypal' => __('Không tạo được liên kết PayPal. Vui lòng thử lại.')]);
            }

            return redirect()->away($approvalUrl);
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['paypal' => __('Không thể mở thanh toán PayPal. Vui lòng thử lại.')]);
        }
    }

    public function momoResume(Request $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->customer_id === $request->user()->id, 403);

        if (! $booking->isBankTransferAwaitingReference()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Đơn này không còn ở trạng thái chờ thanh toán.'));
        }

        if ($booking->payment_provider !== BookingPaymentProvider::Momo) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Đơn này không phải thanh toán MoMo.'));
        }

        if ($booking->hold_expires_at && $booking->hold_expires_at->isPast()) {
            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['momo' => __('Thời giữ chỗ đã hết. Vui lòng tạo đơn mới nếu cần.')]);
        }

        try {
            $payUrl = $this->moMoCheckoutService->createCheckoutRedirectUrl($booking);
            if (! $payUrl) {
                return redirect()
                    ->route('customer.bookings.show', $booking)
                    ->withErrors(['momo' => __('Không tạo được liên kết MoMo. Vui lòng thử lại.')]);
            }

            return redirect()->away($payUrl);
        } catch (Throwable $e) {
            report($e);

            $detail = trim($e->getMessage());
            $detail = mb_substr($detail, 0, 200);

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->withErrors(['momo' => __('Không thể mở thanh toán MoMo. :detail', ['detail' => $detail])]);
        }
    }

    public function momoReturn(Request $request): RedirectResponse
    {
        // MoMo typically calls back with query params.
        $orderId = (string) ($request->input('orderId') ?? $request->input('order_id') ?? '');
        $resultCode = (string) ($request->input('resultCode') ?? $request->input('result_code') ?? '');
        $transId = (string) ($request->input('transId') ?? $request->input('trans_id') ?? '');

        if ($orderId === '') {
            return redirect()
                ->route('customer.bookings.index')
                ->withErrors(['momo' => __('Dữ liệu trả về từ MoMo không hợp lệ.')]);
        }

        $booking = Booking::query()
            ->where('momo_order_id', $orderId)
            ->orWhere('booking_code', $orderId)
            ->first();

        if (! $booking) {
            return redirect()
                ->route('customer.bookings.index')
                ->withErrors(['momo' => __('Không tìm thấy đơn phù hợp với mã thanh toán từ MoMo.')]);
        }

        abort_unless($booking->customer_id === $request->user()->id, 403);

        if ($resultCode === '0' || (int) $resultCode === 0) {
            if ($booking->payment_status !== BookingPaymentStatus::Paid) {
                $booking->forceFill([
                    'payment_status' => BookingPaymentStatus::Paid,
                    'payment_reference' => $transId !== '' ? $transId : $booking->payment_reference,
                ])->save();

                $this->bookingLedgerService->recordMarkedPaid($booking, $request->user());
                $this->bookingNotificationService->sendCreated($booking);
            }

            return redirect()
                ->route('customer.bookings.show', $booking)
                ->with('status', __('Thanh toán MoMo thành công.'));
        }

        return redirect()
            ->route('customer.bookings.show', $booking)
            ->withErrors(['momo' => __('Thanh toán MoMo không thành công. Vui lòng thử lại.')]);
    }
}
