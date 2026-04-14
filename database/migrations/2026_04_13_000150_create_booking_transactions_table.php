<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('pending');
            $table->decimal('amount', 12, 2);
            $table->char('currency', 3)->default('VND');
            $table->string('event_key', 80)->nullable()->unique();
            $table->string('reference', 120)->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('performed_at');
            $table->timestamps();

            $table->index(['booking_id', 'type'], 'bt_booking_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_transactions');
    }
};
