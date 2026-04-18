<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('role');
            }
        });

        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table): void {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('hotel_favorites')) {
            Schema::create('hotel_favorites', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['user_id', 'hotel_id']);
            });
        }

        if (! Schema::hasTable('booking_messages')) {
            Schema::create('booking_messages', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
                $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
                $table->text('body');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['booking_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_messages');
        Schema::dropIfExists('hotel_favorites');

        if (Schema::hasTable('personal_access_tokens')) {
            Schema::dropIfExists('personal_access_tokens');
        }

        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('is_active');
            });
        }
    }
};
