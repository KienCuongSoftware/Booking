<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cancellation_policy_tiers')) {
            Schema::table('cancellation_policy_tiers', function (Blueprint $table): void {
                $table->index(['cancellation_policy_id', 'sort_order'], 'cpt_policy_sort_idx');
            });

            return;
        }

        Schema::create('cancellation_policy_tiers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('cancellation_policy_id')->constrained('cancellation_policies')->cascadeOnDelete();
            $table->unsignedInteger('min_hours_before')->default(0);
            $table->unsignedInteger('max_hours_before')->nullable();
            $table->decimal('fee_percent', 5, 2);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['cancellation_policy_id', 'sort_order'], 'cpt_policy_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cancellation_policy_tiers');
    }
};
