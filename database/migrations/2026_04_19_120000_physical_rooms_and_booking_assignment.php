<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('physical_rooms', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->cascadeOnDelete();
            $table->string('label', 80);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['room_type_id', 'is_active']);
        });

        Schema::table('bookings', function (Blueprint $table): void {
            $table->foreignId('physical_room_id')->nullable()->after('room_type_id')->constrained('physical_rooms')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('physical_room_id');
        });

        Schema::dropIfExists('physical_rooms');
    }
};
