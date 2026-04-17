<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->string('city')->nullable();
            $table->enum('role', ['reseller', 'admin', 'delivery'])->default('reseller');
            $table->enum('status', ['pending', 'active', 'banned'])->default('pending');
            $table->string('otp_code', 6)->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('phone_verified')->default(false);
            $table->string('fcm_token')->nullable(); // Firebase push notifications
            $table->string('avatar')->nullable();    // Profile photo path
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
