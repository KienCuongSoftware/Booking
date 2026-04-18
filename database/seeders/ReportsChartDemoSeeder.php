<?php

namespace Database\Seeders;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Models\RoomType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Seeds many customers + bookings so host "Báo cáo" charts (last 6 months by created_at) show rich data.
 *
 * Run: php artisan db:seed --class=ReportsChartDemoSeeder
 *
 * Optional .env: REPORT_DEMO_CUSTOMERS=800 REPORT_DEMO_BOOKINGS=3500
 * Re-run safe: removes prior demo rows (booking_code RDEMO-*, users reports-demo-*@example.test).
 */
class ReportsChartDemoSeeder extends Seeder
{
    public function run(): void
    {
        $customerCount = max(100, min(5000, (int) env('REPORT_DEMO_CUSTOMERS', 800)));
        $bookingCount = max(500, min(20000, (int) env('REPORT_DEMO_BOOKINGS', 3500)));

        $roomTypes = RoomType::query()
            ->where('is_active', true)
            ->get(['id', 'hotel_id', 'base_price', 'new_price', 'max_occupancy']);

        if ($roomTypes->isEmpty()) {
            $this->command?->warn('No active room types; skip ReportsChartDemoSeeder.');

            return;
        }

        $slots = $roomTypes->map(fn (RoomType $rt) => [
            'hotel_id' => $rt->hotel_id,
            'room_type_id' => $rt->id,
            'unit_price' => (float) ($rt->new_price ?? $rt->base_price ?: 500_000),
            'max_occupancy' => max(1, (int) $rt->max_occupancy),
        ])->values()->all();

        DB::transaction(function () use ($customerCount, $bookingCount, $slots): void {
            DB::table('bookings')->where('booking_code', 'like', 'RDEMO-%')->delete();
            DB::table('users')->where('email', 'like', 'reports-demo-%@example.test')->delete();

            $now = now();
            $passwordHash = Hash::make('password');
            $userChunk = 200;

            for ($offset = 0; $offset < $customerCount; $offset += $userChunk) {
                $take = min($userChunk, $customerCount - $offset);
                $userRows = [];
                for ($i = 0; $i < $take; $i++) {
                    $idx = $offset + $i;
                    $userRows[] = [
                        'name' => 'Demo Khách '.$idx,
                        'email' => 'reports-demo-'.$idx.'@example.test',
                        'google_id' => null,
                        'avatar' => null,
                        'password' => $passwordHash,
                        'role' => UserRole::Customer->value,
                        'email_verified_at' => $now,
                        'remember_token' => Str::random(10),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('users')->insert($userRows);
            }

            $customerIds = DB::table('users')
                ->where('email', 'like', 'reports-demo-%@example.test')
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $monthsWindow = 6;
            $rangeStart = now()->copy()->subMonths($monthsWindow - 1)->startOfMonth();
            $rangeEnd = now()->copy()->endOfMonth();
            $startTs = $rangeStart->getTimestamp();
            $endTs = $rangeEnd->getTimestamp();

            $bookingChunk = 400;
            $statusPicker = static function (): BookingStatus {
                $r = random_int(1, 100);
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
            };

            for ($b = 0; $b < $bookingCount; $b += $bookingChunk) {
                $take = min($bookingChunk, $bookingCount - $b);
                $rows = [];
                for ($k = 0; $k < $take; $k++) {
                    $global = $b + $k;
                    $slot = $slots[$global % count($slots)];
                    $customerId = $customerIds[array_rand($customerIds)];
                    $nights = random_int(1, 7);
                    $unit = max(100_000.0, $slot['unit_price']);
                    $total = round($unit * $nights, 2);
                    $status = $statusPicker();

                    $createdAt = Carbon::createFromTimestamp(random_int($startTs, $endTs));

                    $checkIn = $createdAt->copy()->addDays(random_int(5, 120))->startOfDay();
                    $checkOut = $checkIn->copy()->addDays($nights);

                    $confirmedAt = null;
                    $cancelledAt = null;
                    $completedAt = null;
                    $noShowAt = null;
                    $cancelReason = null;
                    $cancellationFeeAmount = null;
                    $refundAmount = null;
                    $cancellationPolicySnapshot = null;

                    if ($status === BookingStatus::Confirmed) {
                        $confirmedAt = $checkIn->copy()->subDays(random_int(1, 4));
                    } elseif ($status === BookingStatus::Completed) {
                        $confirmedAt = $checkIn->copy()->subDays(random_int(2, 6));
                        $completedAt = $checkOut->copy()->addDay();
                    } elseif ($status === BookingStatus::Cancelled) {
                        $cancelledAt = $createdAt->copy()->addDays(random_int(0, 5));
                        $cancelReason = 'Demo seed';
                        $cancellationFeeAmount = round($total * 0.25, 2);
                        $refundAmount = round($total - $cancellationFeeAmount, 2);
                        $cancellationPolicySnapshot = json_encode(['policy_name' => 'Demo', 'fee_percent' => 25]);
                    } elseif ($status === BookingStatus::NoShow) {
                        $confirmedAt = $checkIn->copy()->subDays(random_int(2, 5));
                        $noShowAt = $checkIn->copy()->addDay();
                        $cancelReason = 'Demo no-show';
                        $cancellationFeeAmount = round($total * 0.5, 2);
                        $refundAmount = round($total - $cancellationFeeAmount, 2);
                        $cancellationPolicySnapshot = json_encode(['policy_name' => 'Demo', 'fee_percent' => 50]);
                    }

                    $paymentMethod = random_int(0, 1) === 0
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

                    $code = 'RDEMO-'.strtoupper(bin2hex(random_bytes(6)));
                    $guestCap = min(10, $slot['max_occupancy']);

                    $rows[] = [
                        'booking_code' => $code,
                        'customer_id' => $customerId,
                        'hotel_id' => $slot['hotel_id'],
                        'room_type_id' => $slot['room_type_id'],
                        'check_in_date' => $checkIn->toDateString(),
                        'check_out_date' => $checkOut->toDateString(),
                        'guest_count' => random_int(1, max(1, $guestCap)),
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
                }
                DB::table('bookings')->insert($rows);
            }
        });

        $this->command?->info("ReportsChartDemoSeeder: {$customerCount} customers, {$bookingCount} bookings (codes RDEMO-*). Password: password");
    }
}
