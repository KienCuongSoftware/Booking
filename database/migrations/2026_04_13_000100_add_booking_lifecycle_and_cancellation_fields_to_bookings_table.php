<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('confirmed_at')->nullable()->after('status');
            $table->timestamp('cancelled_at')->nullable()->after('confirmed_at');
            $table->timestamp('completed_at')->nullable()->after('cancelled_at');
            $table->timestamp('status_changed_at')->nullable()->after('completed_at');
            $table->foreignId('status_changed_by')->nullable()->after('status_changed_at')->constrained('users')->nullOnDelete();
            $table->foreignId('cancelled_by')->nullable()->after('status_changed_by')->constrained('users')->nullOnDelete();
            $table->text('cancel_reason')->nullable()->after('host_note');
            $table->decimal('cancellation_fee_amount', 12, 2)->nullable()->after('cancel_reason');
            $table->decimal('refund_amount', 12, 2)->nullable()->after('cancellation_fee_amount');
            $table->json('cancellation_policy_snapshot')->nullable()->after('refund_amount');
            $table->timestamp('reminder_sent_at')->nullable()->after('cancellation_policy_snapshot');
            $table->timestamp('follow_up_sent_at')->nullable()->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('status_changed_by');
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn([
                'confirmed_at',
                'cancelled_at',
                'completed_at',
                'status_changed_at',
                'cancel_reason',
                'cancellation_fee_amount',
                'refund_amount',
                'cancellation_policy_snapshot',
                'reminder_sent_at',
                'follow_up_sent_at',
            ]);
        });
    }
};
