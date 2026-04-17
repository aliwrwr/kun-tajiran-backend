<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('province_name');        // اسم المحافظة بالعربي
            $table->string('province_name_en')->nullable();
            $table->unsignedBigInteger('base_fee'); // أجرة التوصيل الأساسية
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                           // اسم العرض
            $table->enum('discount_type', ['fixed', 'percentage', 'free']);   // نوع الخصم
            $table->unsignedInteger('discount_value')->default(0);            // قيمة الخصم
            $table->enum('applies_to', ['all', 'specific_sellers'])->default('all');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('delivery_offer_sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_offer_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unique(['delivery_offer_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_offer_sellers');
        Schema::dropIfExists('delivery_offers');
        Schema::dropIfExists('delivery_zones');
    }
};
