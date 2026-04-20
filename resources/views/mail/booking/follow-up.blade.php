<x-mail::message>
# {{ __('Cảm ơn bạn đã lưu trú') }}

{{ __('Kỳ lưu trú của bạn đã hoàn tất. Cảm ơn bạn đã tin tưởng :app.', ['app' => config('app.name')]) }}

<x-mail::panel>
{{ __('Mã đơn') }}: **{{ $booking->booking_code }}**  
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**
</x-mail::panel>

{{ __('Nếu có nhu cầu, bạn có thể đặt lại ngay từ lịch sử đơn đặt của mình.') }}

@if (! $booking->review()->exists())
@php
    $reviewUrl = route('customer.bookings.review.create', $booking, absolute: true);
@endphp
{{ __('Nếu bạn chưa đánh giá, hãy dành một phút chia sẻ trải nghiệm của bạn.') }}

<x-mail::button :url="$reviewUrl">
{{ __('Viết đánh giá') }}
</x-mail::button>
@endif

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
