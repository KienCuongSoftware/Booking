<x-mail::message>
# {{ __('Nhắc lịch nhận phòng') }}

{{ __('Bạn có lịch nhận phòng vào ngày mai.') }}

<x-mail::panel>
{{ __('Mã đơn') }}: **{{ $booking->booking_code }}**  
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**  
{{ __('Nhận phòng') }}: **{{ $booking->check_in_date->format('d/m/Y') }}**
</x-mail::panel>

{{ __('Chúc bạn có chuyến đi thật tuyệt vời.') }}

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
