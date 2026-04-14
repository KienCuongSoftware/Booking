<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->timestamp('no_show_at')->nullable()->after('completed_at');
            $table->timestamp('reminder_d3_sent_at')->nullable()->after('reminder_sent_at');
            $table->timestamp('reminder_h6_sent_at')->nullable()->after('reminder_d3_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table): void {
            $table->dropColumn([
                'no_show_at',
                'reminder_d3_sent_at',
                'reminder_h6_sent_at',
            ]);
        });
    }
};
