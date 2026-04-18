<?php

namespace Database\Seeders;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\Hotel;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Dữ liệu mẫu giống thật: tên khách Việt, đơn RSIM-*, khach.XXXX@sim-booking.local,
 * mã giảm giá PCODE-SIM-* trên mỗi khách sạn.
 *
 * php artisan db:seed --class=RealisticMassDataSeeder
 *
 * .env:
 *   REALISTIC_SIM_CUSTOMERS=500
 *   REALISTIC_SIM_BOOKINGS_PER_CUSTOMER=500
 *   REALISTIC_SIM_BATCH=800
 *
 * Giới hạn an toàn: tối đa 800 khách × 800 đơn, tổng tối đa 400.000 đơn.
 * Xoá trước: RDEMO-*, RSIM-*, reports-demo-*, khach.*@sim-booking.local, PCODE-SIM-*.
 */
class RealisticMassDataSeeder extends Seeder
{
    private const HO = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Vũ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Phan', 'Vương', 'Dương', 'Lý', 'Tôn'];

    private const DEM = ['Văn', 'Thị', 'Minh', 'Thu', 'Hồng', 'Đức', 'Quang', 'Thanh', 'Tuấn', 'Hà', 'Lan', 'Nam', 'Huy', 'Mai', 'Anh', 'Dũng', 'Chi', 'Phương', 'Giang', 'Long'];

    private const TEN = ['An', 'Bình', 'Chi', 'Dũng', 'Hà', 'Huy', 'Lan', 'Long', 'Mai', 'Nam', 'Oanh', 'Phúc', 'Quyên', 'Tuấn', 'Uyên', 'Yến', 'Khoa', 'Linh', 'My', 'Nga', 'Phong', 'Quân', 'Sơn', 'Thảo', 'Trang', 'Việt', 'Xuân', 'Yên', 'Bảo', 'Cường', 'Diệu', 'Đạt', 'Hải', 'Khánh', 'Lộc', 'Nhi', 'Tâm', 'Vân', 'Đan', 'Hạnh'];

    public function run(): void
    {
        $customerCount = max(1, min(800, (int) env('REALISTIC_SIM_CUSTOMERS', 500)));
        $perCustomer = max(1, min(800, (int) env('REALISTIC_SIM_BOOKINGS_PER_CUSTOMER', 500)));
        $batchSize = max(100, min(5000, (int) env('REALISTIC_SIM_BATCH', 800)));

        $maxTotal = 400_000;
        $totalBookings = $customerCount * $perCustomer;
        if ($totalBookings > $maxTotal) {
            $this->command?->error("Tổng đơn {$totalBookings} vượt giới hạn {$maxTotal}.");

            return;
        }

        $roomTypes = RoomType::query()
            ->where('is_active', true)
            ->get(['id', 'hotel_id', 'base_price', 'new_price', 'max_occupancy']);

        if ($roomTypes->isEmpty()) {
            $this->command?->warn('Không có loại phòng active — bỏ qua RealisticMassDataSeeder.');

            return;
        }

        $slots = $roomTypes->map(fn (RoomType $rt) => [
            'hotel_id' => $rt->hotel_id,
            'room_type_id' => $rt->id,
            'unit_price' => (float) ($rt->new_price ?? $rt->base_price ?: 500_000),
            'max_occupancy' => max(1, (int) $rt->max_occupancy),
        ])->values()->all();

        $now = now();
        $passwordHash = Hash::make('password');

        DB::transaction(function () use ($customerCount, $now, $passwordHash): void {
            $this->purgeOldSimData();

            $userRows = [];
            for ($i = 0; $i < $customerCount; $i++) {
                $email = 'khach.'.str_pad((string) $i, 4, '0', STR_PAD_LEFT).'@sim-booking.local';
                $userRows[] = [
                    'name' => $this->vietnameseName($i),
                    'email' => $email,
                    'google_id' => null,
                    'avatar' => null,
                    'password' => $passwordHash,
                    'role' => UserRole::Customer->value,
                    'is_active' => true,
                    'email_verified_at' => $now,
                    'remember_token' => Str::random(10),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($userRows, 200) as $chunk) {
                DB::table('users')->insert($chunk);
            }
        });

        $this->seedPromoCodes();

        $customerIds = DB::table('users')
            ->where('email', 'like', 'khach.%@sim-booking.local')
            ->orderBy('email')
            ->pluck('id')
            ->values()
            ->all();

        if (count($customerIds) !== $customerCount) {
            throw new \RuntimeException('Số khách sau insert không khớp.');
        }

        $monthsWindow = 6;
        $rangeStart = now()->copy()->subMonths($monthsWindow - 1)->startOfMonth();
        $rangeEnd = now()->copy()->endOfMonth();
        $startTs = $rangeStart->getTimestamp();
        $span = max(1, $rangeEnd->getTimestamp() - $startTs);

        $buffer = [];
        $seq = 0;

        for ($c = 0; $c < $customerCount; $c++) {
            $customerId = (int) $customerIds[$c];

            for ($b = 0; $b < $perCustomer; $b++) {
                $seq++;
                $slot = $slots[$seq % count($slots)];
                $nights = ($seq % 7) + 1;
                $unit = max(100_000.0, $slot['unit_price']);
                $total = round($unit * $nights, 2);
                $status = $this->pickStatus($seq);

                $createdAt = Carbon::createFromTimestamp($startTs + (($seq * 977) % $span));
                $checkIn = $createdAt->copy()->addDays(5 + (($seq * 13 + $b * 3 + $c) % 86))->startOfDay();
                $checkOut = $checkIn->copy()->addDays($nights);

                [$confirmedAt, $cancelledAt, $completedAt, $noShowAt, $cancelReason, $cancellationFeeAmount, $refundAmount, $cancellationPolicySnapshot] =
                    $this->statusDates($status, $createdAt, $checkIn, $checkOut, $total, $seq);

                $paymentMethod = ($seq % 3 === 0)
                    ? BookingPaymentMethod::Cash
                    : BookingPaymentMethod::BankTransfer;

                $paymentStatus = match ($status) {
                    BookingStatus::Cancelled, BookingStatus::NoShow => BookingPaymentStatus::Failed,
                    BookingStatus::Completed => BookingPaymentStatus::Paid,
                    BookingStatus::Confirmed => BookingPaymentStatus::Paid,
                    default => $paymentMethod === BookingPaymentMethod::Cash
                        ? BookingPaymentStatus::Unpaid
                        : BookingPaymentStatus::Pending,
                };

                $code = sprintf('RSIM-%05d-%03d', $c, $b);
                $guestCap = min(10, $slot['max_occupancy']);

                $buffer[] = [
                    'booking_code' => $code,
                    'customer_id' => $customerId,
                    'hotel_id' => $slot['hotel_id'],
                    'room_type_id' => $slot['room_type_id'],
                    'check_in_date' => $checkIn->toDateString(),
                    'check_out_date' => $checkOut->toDateString(),
                    'guest_count' => (($seq + $c + $b) % $guestCap) + 1,
                    'nights' => $nights,
                    'unit_price' => $unit,
                    'total_price' => $total,
                    'currency' => 'VND',
                    'status' => $status->value,
                    'payment_method' => $paymentMethod->value,
                    'payment_provider' => null,
                    'payment_status' => $paymentStatus->value,
                    'payment_reference' => null,
                    'customer_note' => null,
                    'host_note' => null,
                    'confirmed_at' => $confirmedAt,
                    'cancelled_at' => $cancelledAt,
                    'completed_at' => $completedAt,
                    'no_show_at' => $noShowAt,
                    'status_changed_at' => $noShowAt ?? $cancelledAt ?? $completedAt ?? $confirmedAt ?? $createdAt,
                    'status_changed_by' => $customerId,
                    'cancelled_by' => $cancelledAt ? $customerId : null,
                    'cancel_reason' => $cancelReason,
                    'cancellation_fee_amount' => $cancellationFeeAmount,
                    'refund_amount' => $refundAmount,
                    'cancellation_policy_snapshot' => $cancellationPolicySnapshot,
                    'reminder_sent_at' => null,
                    'follow_up_sent_at' => null,
                    'reminder_d3_sent_at' => null,
                    'reminder_h6_sent_at' => null,
                    'hold_expires_at' => null,
                    'idempotency_key' => null,
                    'paypal_order_id' => null,
                    'paypal_capture_id' => null,
                    'promo_code_id' => null,
                    'discount_amount' => 0,
                    'internal_tags' => null,
                    'check_in_token' => null,
                    'checked_in_at' => null,
                    'momo_order_id' => null,
                    'pricing_snapshot' => null,
                    'pending_host_notified_at' => null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if (count($buffer) >= $batchSize) {
                    DB::table('bookings')->insert($buffer);
                    $buffer = [];
                    if ($seq % 50_000 === 0) {
                        $this->command?->info("Đã chèn {$seq} đơn…");
                    }
                }
            }
        }

        if ($buffer !== []) {
            DB::table('bookings')->insert($buffer);
        }

        $this->command?->info("RealisticMassDataSeeder xong: {$customerCount} khách, {$totalBookings} đơn RSIM-*, mã PCODE-SIM-* trên mỗi KS. Mật khẩu: password");
    }

    private function purgeOldSimData(): void
    {
        DB::table('bookings')->where('booking_code', 'like', 'RDEMO-%')->delete();
        DB::table('bookings')->where('booking_code', 'like', 'RSIM-%')->delete();
        DB::table('users')->where('email', 'like', 'reports-demo-%@example.test')->delete();
        DB::table('users')->where('email', 'like', 'khach.%@sim-booking.local')->delete();
        DB::table('promo_codes')->where('code', 'like', 'PCODE-SIM-%')->delete();
    }

    private function seedPromoCodes(): void
    {
        $hotels = Hotel::query()->orderBy('id')->get(['id']);
        if ($hotels->isEmpty()) {
            return;
        }

        $from = now()->subMonth()->startOfMonth()->toDateString();
        $to = now()->addMonths(6)->endOfMonth()->toDateString();
        $rows = [];

        foreach ($hotels as $hotel) {
            $suffix = str_pad((string) $hotel->id, 4, '0', STR_PAD_LEFT);
            $rows[] = [
                'code' => 'PCODE-SIM-WEL'.$suffix,
                'hotel_id' => $hotel->id,
                'room_type_id' => null,
                'valid_from' => $from,
                'valid_to' => $to,
                'discount_type' => 'percent',
                'discount_value' => 10,
                'max_uses' => 50_000,
                'uses_count' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $rows[] = [
                'code' => 'PCODE-SIM-FIX'.$suffix,
                'hotel_id' => $hotel->id,
                'room_type_id' => null,
                'valid_from' => $from,
                'valid_to' => $to,
                'discount_type' => 'fixed',
                'discount_value' => 150_000,
                'max_uses' => 20_000,
                'uses_count' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($rows, 40) as $chunk) {
            DB::table('promo_codes')->insert($chunk);
        }
    }

    private function vietnameseName(int $index): string
    {
        $ho = self::HO[$index % count(self::HO)];
        $dem = self::DEM[($index * 3) % count(self::DEM)];
        $ten = self::TEN[($index * 5) % count(self::TEN)];

        return $ho.' '.$dem.' '.$ten;
    }

    private function pickStatus(int $seq): BookingStatus
    {
        $r = ($seq % 100) + 1;
        if ($r <= 38) {
            return BookingStatus::Confirmed;
        }
        if ($r <= 68) {
            return BookingStatus::Completed;
        }
        if ($r <= 82) {
            return BookingStatus::Cancelled;
        }
        if ($r <= 92) {
            return BookingStatus::NoShow;
        }

        return BookingStatus::Pending;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon, 2: ?Carbon, 3: ?Carbon, 4: ?string, 5: ?float, 6: ?float, 7: ?string}
     */
    private function statusDates(BookingStatus $status, Carbon $createdAt, Carbon $checkIn, Carbon $checkOut, float $total, int $seq): array
    {
        $confirmedAt = null;
        $cancelledAt = null;
        $completedAt = null;
        $noShowAt = null;
        $cancelReason = null;
        $cancellationFeeAmount = null;
        $refundAmount = null;
        $cancellationPolicySnapshot = null;

        if ($status === BookingStatus::Confirmed) {
            $confirmedAt = $checkIn->copy()->subDays(1 + ($seq % 3));
        } elseif ($status === BookingStatus::Completed) {
            $confirmedAt = $checkIn->copy()->subDays(2 + ($seq % 4));
            $completedAt = $checkOut->copy()->addDay();
        } elseif ($status === BookingStatus::Cancelled) {
            $cancelledAt = $createdAt->copy()->addDays($seq % 6);
            $cancelReason = 'Hủy theo mô phỏng';
            $cancellationFeeAmount = round($total * 0.25, 2);
            $refundAmount = round($total - $cancellationFeeAmount, 2);
            $cancellationPolicySnapshot = json_encode(['policy_name' => 'Mẫu', 'fee_percent' => 25]);
        } elseif ($status === BookingStatus::NoShow) {
            $confirmedAt = $checkIn->copy()->subDays(2 + ($seq % 3));
            $noShowAt = $checkIn->copy()->addDay();
            $cancelReason = 'No-show mô phỏng';
            $cancellationFeeAmount = round($total * 0.5, 2);
            $refundAmount = round($total - $cancellationFeeAmount, 2);
            $cancellationPolicySnapshot = json_encode(['policy_name' => 'Mẫu', 'fee_percent' => 50]);
        }

        return [$confirmedAt, $cancelledAt, $completedAt, $noShowAt, $cancelReason, $cancellationFeeAmount, $refundAmount, $cancellationPolicySnapshot];
    }
}
