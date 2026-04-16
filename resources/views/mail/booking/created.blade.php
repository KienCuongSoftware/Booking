<x-mail::message>
# {{ __('Đơn đặt phòng mới') }}

{{ $recipientRole === 'host'
    ? __('Bạn vừa nhận được một yêu cầu đặt phòng mới từ khách hàng.')
    : __('Yêu cầu đặt phòng của bạn đã được ghi nhận.') }}

@if (! empty($intro))
<x-mail::panel>
{!! nl2br(e($intro)) !!}
</x-mail::panel>
@endif

<x-mail::panel>
{{ __('Mã đơn') }}: **{{ $booking->booking_code }}**  
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**  
{{ __('Loại phòng') }}: **{{ $booking->roomType->name }}**  
{{ __('Lưu trú') }}: **{{ $booking->check_in_date->format('d/m/Y') }} → {{ $booking->check_out_date->format('d/m/Y') }} ({{ $booking->nights }} {{ __('đêm') }})**  
{{ __('Tổng tiền') }}: **{{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}**
</x-mail::panel>

{{ __('Chúng tôi sẽ tiếp tục cập nhật trạng thái qua email.') }}

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
