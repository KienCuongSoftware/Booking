<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ __('Hóa đơn') }} {{ $booking->booking_code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ __('Hóa đơn / Phiếu thanh toán') }}</h1>
    <p><strong>{{ __('Mã đơn') }}:</strong> {{ $booking->booking_code }}</p>
    <p><strong>{{ __('Khách') }}:</strong> {{ $booking->customer?->name }} — {{ $booking->customer?->email }}</p>
    <p><strong>{{ __('Khách sạn') }}:</strong> {{ $booking->hotel?->name }}</p>
    <p><strong>{{ __('Địa chỉ') }}:</strong> {{ $booking->hotel?->address }}, {{ $booking->hotel?->city }}</p>
    <p><strong>{{ __('Loại phòng') }}:</strong> {{ $booking->roomType?->name }}</p>
    <p><strong>{{ __('Nhận / Trả') }}:</strong> {{ $booking->check_in_date->format('d/m/Y') }} — {{ $booking->check_out_date->format('d/m/Y') }} ({{ $booking->nights }} {{ __('đêm') }})</p>
    <table>
        <tr><th>{{ __('Mô tả') }}</th><th>{{ __('Số tiền') }}</th></tr>
        <tr><td>{{ __('Tổng tiền đặt phòng') }}</td><td>{{ number_format((float) $booking->total_price, 0, ',', '.') }} {{ $booking->currency }}</td></tr>
        @if ((float) $booking->discount_amount > 0)
            <tr><td>{{ __('Giảm giá') }}</td><td>-{{ number_format((float) $booking->discount_amount, 0, ',', '.') }} {{ $booking->currency }}</td></tr>
        @endif
    </table>
    <p style="margin-top:16px;font-size:11px;color:#666;">{{ __('Chứng từ điện tử được tạo từ hệ thống Booking. Không thay thế hóa đơn GTGT nếu không có mã số thuế.') }}</p>
</body>
</html>
