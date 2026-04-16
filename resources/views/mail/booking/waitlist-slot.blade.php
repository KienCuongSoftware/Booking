<x-mail::message>
# {{ __('Có chỗ trống cho lịch bạn quan tâm') }}

{{ __('Loại phòng :room tại :hotel vừa có thêm chỗ trong khoảng ngày bạn đăng ký chờ.', ['room' => $entry->roomType->name, 'hotel' => $entry->hotel->name]) }}

<x-mail::panel>
{{ __('Nhận phòng') }}: **{{ $entry->check_in_date->format('d/m/Y') }}**  
{{ __('Trả phòng') }}: **{{ $entry->check_out_date->format('d/m/Y') }}**
</x-mail::panel>

<x-mail::button :url="route('public.hotels.show', $entry->hotel)">
{{ __('Xem khách sạn và đặt lại') }}
</x-mail::button>

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
