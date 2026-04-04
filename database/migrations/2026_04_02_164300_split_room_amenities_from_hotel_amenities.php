<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('amenity_room_type');

        Schema::create('room_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('icon_key', 64)->nullable();
            $table->string('category_key', 64)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('room_amenity_room_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_amenity_id')->constrained('room_amenities')->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['room_amenity_id', 'room_type_id'], 'room_amenity_room_type_unique');
        });

        if (Schema::hasColumn('amenities', 'context')) {
            DB::table('amenities')->where('context', 'room')->delete();
            DB::table('amenities')->where('icon_key', 'like', 'rt-%')->delete();

            Schema::table('amenities', function (Blueprint $table) {
                $table->dropColumn(['context', 'category_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('room_amenity_room_type');
        Schema::dropIfExists('room_amenities');

        Schema::create('amenity_room_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_type_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['amenity_id', 'room_type_id']);
        });

        Schema::table('amenities', function (Blueprint $table) {
            $table->string('context', 16)->default('hotel')->after('icon_key');
            $table->string('category_key', 64)->nullable()->after('context');
        });
    }
};
