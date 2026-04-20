<?php

namespace App\Models;

use App\Enums\BookingPaymentMethod;
use App\Enums\BookingPaymentProvider;
use App\Enums\BookingPaymentStatus;
use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'customer_id',
        'hotel_id',
        'room_type_id',
        'physical_room_id',
        'check_in_date',
        'check_out_date',
        'guest_count',
        'nights',
        'unit_price',
        'total_price',
        'currency',
        'status',
        'confirmed_at',
        'cancelled_at',
        'no_show_at',
        'completed_at',
        'status_changed_at',
        'status_changed_by',
        'cancelled_by',
        'payment_method',
        'payment_provider',
        'payment_status',
        'payment_reference',
        'customer_note',
        'host_note',
        'cancel_reason',
        'cancellation_fee_amount',
        'refund_amount',
        'cancellation_policy_snapshot',
        'reminder_sent_at',
        'reminder_d3_sent_at',
        'reminder_h6_sent_at',
        'follow_up_sent_at',
        'hold_expires_at',
        'idempotency_key',
        'paypal_order_id',
        'paypal_capture_id',
        'promo_code_id',
        'discount_amount',
        'internal_tags',
        'check_in_token',
        'checked_in_at',
        'momo_order_id',
        'pricing_snapshot',
        'pending_host_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'guest_count' => 'integer',
            'nights' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'no_show_at' => 'datetime',
            'completed_at' => 'datetime',
            'status_changed_at' => 'datetime',
            'status_changed_by' => 'integer',
            'cancelled_by' => 'integer',
            'payment_method' => BookingPaymentMethod::class,
            'payment_provider' => BookingPaymentProvider::class,
            'payment_status' => BookingPaymentStatus::class,
            'cancellation_fee_amount' => 'decimal:2',
            'refund_amount' => 'decimal:2',
            'cancellation_policy_snapshot' => 'array',
            'reminder_sent_at' => 'datetime',
            'reminder_d3_sent_at' => 'datetime',
            'reminder_h6_sent_at' => 'datetime',
            'follow_up_sent_at' => 'datetime',
            'hold_expires_at' => 'datetime',
            'discount_amount' => 'decimal:2',
            'internal_tags' => 'array',
            'checked_in_at' => 'datetime',
            'pricing_snapshot' => 'array',
            'pending_host_notified_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function physicalRoom(): BelongsTo
    {
        return $this->belongsTo(PhysicalRoom::class);
    }

    public function statusChangedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_changed_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(BookingStatusEvent::class)->orderByDesc('changed_at');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BookingTransaction::class)->latest('id');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(BookingMessage::class)->orderBy('id');
    }

    public function isPayPalCheckoutPending(): bool
    {
        return $this->status === BookingStatus::Pending
            && $this->payment_method === BookingPaymentMethod::PayPal
            && $this->payment_status === BookingPaymentStatus::Pending;
    }

    public function isBankTransferAwaitingReference(): bool
    {
        return $this->status === BookingStatus::Pending
            && $this->payment_method === BookingPaymentMethod::BankTransfer
            && in_array($this->payment_status, [BookingPaymentStatus::Pending, BookingPaymentStatus::Unpaid], true);
    }
}
