<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 40)->unique();
            $table->foreignId('hotel_id')->nullable()->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->nullable()->constrained('room_types')->cascadeOnDelete();
            $table->date('valid_from');
            $table->date('valid_to');
            $table->string('discount_type', 20);
            $table->decimal('discount_value', 12, 2);
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('uses_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('public_holidays', function (Blueprint $table): void {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('name');
            $table->string('country', 2)->default('VN');
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 120);
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('scope', 64);
            $table->string('key', 128);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['scope', 'key', 'user_id']);
        });

        Schema::create('webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider', 32);
            $table->string('external_id', 191)->unique();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->string('event_type', 120)->nullable();
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();
        });

        Schema::create('waitlist_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('guest_count');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'hotel_id', 'room_type_id', 'check_in_date', 'check_out_date'], 'waitlist_user_room_dates');
        });

        Schema::create('reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::table('hotels', function (Blueprint $table): void {
            $table->decimal('weekend_multiplier', 8, 4)->default(1.1000)->after('new_price');
            $table->decimal('holiday_multiplier', 8, 4)->default(1.2500)->after('weekend_multiplier');
            $table->unsignedSmallInteger('last_minute_hours')->default(72)->after('holiday_multiplier');
            $table->decimal('last_minute_discount_percent', 8, 2)->default(5.00)->after('last_minute_hours');
            $table->json('email_templates')->nullable()->after('last_minute_discount_percent');
        });

        Schema::table('room_types', function (Blueprint $table): void {
            $table->decimal('weekend_multiplier', 8, 4)->nullable()->after('new_price');
            $table->decimal('holiday_multiplier', 8, 4)->nullable()->after('weekend_multiplier');
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('hold_expires_at')->nullable()->after('follow_up_sent_at');
            $table->string('idempotency_key', 128)->nullable()->unique()->after('hold_expires_at');
            $table->string('stripe_checkout_session_id', 191)->nullable()->index()->after('idempotency_key');
            $table->string('stripe_payment_intent_id', 191)->nullable()->index()->after('stripe_checkout_session_id');
            $table->foreignId('promo_code_id')->nullable()->after('stripe_payment_intent_id')->constrained('promo_codes')->nullOnDelete();
            $table->decimal('discount_amount', 12, 2)->default(0)->after('promo_code_id');
            $table->json('internal_tags')->nullable()->after('host_note');
            $table->string('check_in_token', 64)->nullable()->unique()->after('internal_tags');
            $table->timestamp('checked_in_at')->nullable()->after('check_in_token');
            $table->string('momo_order_id', 120)->nullable()->index()->after('checked_in_at');
            $table->json('pricing_snapshot')->nullable()->after('momo_order_id');
            $table->timestamp('pending_host_notified_at')->nullable()->after('pricing_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropForeign(['promo_code_id']);
            $table->dropColumn([
                'hold_expires_at',
                'idempotency_key',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'promo_code_id',
                'discount_amount',
                'internal_tags',
                'check_in_token',
                'checked_in_at',
                'momo_order_id',
                'pricing_snapshot',
                'pending_host_notified_at',
            ]);
        });

        Schema::table('room_types', function (Blueprint $table): void {
            $table->dropColumn(['weekend_multiplier', 'holiday_multiplier']);
        });

        Schema::table('hotels', function (Blueprint $table): void {
            $table->dropColumn([
                'weekend_multiplier',
                'holiday_multiplier',
                'last_minute_hours',
                'last_minute_discount_percent',
                'email_templates',
            ]);
        });

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('waitlist_entries');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('public_holidays');
        Schema::dropIfExists('promo_codes');
    }
};
