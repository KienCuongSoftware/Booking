<x-mail::message>
# {{ __('Nhắc lịch nhận phòng') }}

@php
    $windowLabel = match ($reminderWindow) {
        'd3' => __('3 ngày trước nhận phòng'),
        'h6' => __('6 giờ trước nhận phòng'),
        default => __('1 ngày trước nhận phòng'),
    };
@endphp

{{ __('Nhắc lịch ở mốc: :window.', ['window' => $windowLabel]) }}

<x-mail::panel>
{{ __('Mã đơn') }}: **{{ $booking->booking_code }}**  
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**  
{{ __('Nhận phòng') }}: **{{ $booking->check_in_date->format('d/m/Y') }}**
</x-mail::panel>

{{ __('Chúc bạn có chuyến đi thật tuyệt vời.') }}

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
