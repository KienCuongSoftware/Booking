<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bookings', 'paypal_order_id')) {
            return;
        }

        if (Schema::hasColumn('bookings', 'stripe_checkout_session_id')) {
            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropIndex(['stripe_checkout_session_id']);
                $table->dropIndex(['stripe_payment_intent_id']);
            });

            Schema::table('bookings', function (Blueprint $table): void {
                $table->dropColumn(['stripe_checkout_session_id', 'stripe_payment_intent_id']);
            });
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('paypal_order_id', 191)->nullable()->after('idempotency_key')->index();
            $table->string('paypal_capture_id', 191)->nullable()->after('paypal_order_id')->index();
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bookings', 'paypal_order_id')) {
            return;
        }

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropIndex(['paypal_order_id']);
            $table->dropIndex(['paypal_capture_id']);
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn(['paypal_order_id', 'paypal_capture_id']);
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->string('stripe_checkout_session_id', 191)->nullable()->index()->after('idempotency_key');
            $table->string('stripe_payment_intent_id', 191)->nullable()->index()->after('stripe_checkout_session_id');
        });
    }
};
