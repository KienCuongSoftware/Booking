<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->string('booking_code', 32)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedTinyInteger('guest_count');
            $table->unsignedSmallInteger('nights');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->char('currency', 3)->default('VND');
            $table->string('status', 20)->default('pending');
            $table->string('payment_method', 20);
            $table->string('payment_provider', 20)->nullable();
            $table->string('payment_status', 20)->default('unpaid');
            $table->string('payment_reference')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('host_note')->nullable();
            $table->timestamps();

            $table->index(['hotel_id', 'status']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['payment_status', 'payment_method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
