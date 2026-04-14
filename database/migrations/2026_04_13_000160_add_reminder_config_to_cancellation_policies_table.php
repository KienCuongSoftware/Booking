<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cancellation_policies', function (Blueprint $table): void {
            $table->boolean('send_reminder_d3')->default(true)->after('is_active');
            $table->boolean('send_reminder_d1')->default(true)->after('send_reminder_d3');
            $table->boolean('send_reminder_h6')->default(true)->after('send_reminder_d1');
        });
    }

    public function down(): void
    {
        Schema::table('cancellation_policies', function (Blueprint $table): void {
            $table->dropColumn([
                'send_reminder_d3',
                'send_reminder_d1',
                'send_reminder_h6',
            ]);
        });
    }
};
