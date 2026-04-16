<x-mail::message>
# {{ __('Đơn đặt đang chờ quá lâu') }}

{{ __('Bạn có đơn :code đang ở trạng thái chờ xử lý. Vui lòng xác nhận hoặc từ chối sớm để khách nhận được phản hồi.', ['code' => $booking->booking_code]) }}

<x-mail::panel>
{{ __('Khách sạn') }}: **{{ $booking->hotel->name }}**  
{{ __('Loại phòng') }}: **{{ $booking->roomType->name }}**  
{{ __('Nhận phòng') }}: **{{ $booking->check_in_date->format('d/m/Y') }}**
</x-mail::panel>

<x-mail::button :url="route('host.bookings.index', ['status' => 'pending'])">
{{ __('Mở danh sách đơn chờ') }}
</x-mail::button>

{{ __('Trân trọng,') }}<br>
{{ config('app.name') }}
</x-mail::message>
