<x-mail::message>
# {{ __('Trạng thái đơn đã thay đổi') }}

{{ __('Đơn :code của bạn vừa được cập nhật.', ['code' => $booking->booking_code]) }}

<x-mail::panel>
{{ __('Trạng thái cũ') }}: **{{ $fromStatus?->labelVi() ?? __('Không xác định') }}**  
{{ __('Trạng thái mới') }}: **{{ $booking->status->labelVi() }}**
</x-mail::panel>

@if ($booking->status->value === 'cancelled')
{{ __('Phí hủy') }}: **{{ number_format((float) ($booking->cancellation_fee_amount ?? 0), 0, ',', '.') }} {{ $booking->currency }}**  
{{ __('Số tiền hoàn') }}: **{{ number_format((float) ($booking->refund_amount ?? 0), 0, ',', '.') }} {{ $booking->currency }}**
@endif

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
