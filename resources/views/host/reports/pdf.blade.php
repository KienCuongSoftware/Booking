<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ __('Báo cáo vận hành') }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 8px; color: #003580; }
        .muted { color: #555; font-size: 10px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #e8f4fc; font-weight: bold; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ __('Báo cáo vận hành') }}</h1>
    <p class="muted">{{ __('Xuất PDF') }} — {{ $generatedAt }}</p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Tháng') }}</th>
                <th class="num">{{ __('Doanh thu (VND)') }}</th>
                <th class="num">{{ __('Tỉ lệ hủy (%)') }}</th>
                <th class="num">{{ __('Tỉ lệ không đến (%)') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($chartLabels as $i => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="num">{{ number_format((float) ($chartRevenueSeries[$i] ?? 0), 0, ',', '.') }}</td>
                    <td class="num">{{ $chartCancelRateSeries[$i] ?? 0 }}</td>
                    <td class="num">{{ $chartNoShowRateSeries[$i] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="margin-top: 24px; font-size: 14px;">{{ __('Chi tiết đơn') }}</h2>
    <table>
        <thead>
            <tr>
                <th>{{ __('Mã đơn') }}</th>
                <th>{{ __('Ngày tạo') }}</th>
                <th>{{ __('Khách sạn') }}</th>
                <th>{{ __('Phòng') }}</th>
                <th>{{ __('Trạng thái') }}</th>
                <th class="num">{{ __('Tổng tiền') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($bookings as $b)
                <tr>
                    <td>{{ $b->booking_code }}</td>
                    <td>{{ $b->created_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $b->hotel?->name }}</td>
                    <td>{{ $b->roomType?->name }}</td>
                    <td>{{ $b->status instanceof \App\Enums\BookingStatus ? $b->status->value : $b->status }}</td>
                    <td class="num">{{ number_format((float) $b->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
