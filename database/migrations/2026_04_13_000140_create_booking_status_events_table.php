<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->string('note', 255)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['booking_id', 'changed_at'], 'bse_booking_changed_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_events');
    }
};
