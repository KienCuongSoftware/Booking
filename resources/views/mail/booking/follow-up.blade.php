<x-mail::message>
# {{ __('Cảm ơn bạn đã lưu trú') }}

{{ __('Kỳ lưu trú của bạn đã hoàn tất. Cảm ơn bạn đã tin tưởng :app.', ['app' => config('app.name')]) }}

<x-mail::panel>
{{ __('Mã đơn') }}: **{{ $booking->booking_code }}**  
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**
</x-mail::panel>

{{ __('Nếu có nhu cầu, bạn có thể đặt lại ngay từ lịch sử đơn đặt của mình.') }}

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
