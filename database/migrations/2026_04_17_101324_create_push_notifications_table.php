<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('image_url')->nullable();       // Notification image (URL or path)
            $table->string('target_type')->default('all'); // all | role | user
            $table->string('target_role')->nullable();     // reseller | delivery
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('click_action')->nullable();    // deep-link route e.g. /orders
            $table->json('data')->nullable();              // extra key-value pairs
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
