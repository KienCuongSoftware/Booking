<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->decimal('old_price', 12, 2)->nullable()->after('base_price');
            $table->decimal('new_price', 12, 2)->nullable()->after('old_price');
        });

        DB::table('hotels')
            ->whereNull('new_price')
            ->update(['new_price' => DB::raw('base_price')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['old_price', 'new_price']);
        });
    }
};
