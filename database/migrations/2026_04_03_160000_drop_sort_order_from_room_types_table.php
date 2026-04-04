<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('room_types', 'sort_order')) {
            return;
        }

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'is_active', 'sort_order']);
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->index(['hotel_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('room_types', function (Blueprint $table) {
            $table->dropIndex(['hotel_id', 'is_active']);
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->unsignedSmallInteger('sort_order')->default(0)->after('is_active');
        });

        Schema::table('room_types', function (Blueprint $table) {
            $table->index(['hotel_id', 'is_active', 'sort_order']);
        });
    }
};
