<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->json('images'); // array of image paths
            $table->unsignedBigInteger('wholesale_price'); // سعر الجملة (بالدينار العراقي)
            $table->unsignedBigInteger('suggested_price'); // سعر البيع المقترح
            $table->unsignedBigInteger('min_price');       // أقل سعر يسمح بيعه
            $table->unsignedBigInteger('delivery_fee')->default(3000); // رسوم التوصيل
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('weight')->nullable(); // الوزن
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sales_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // profit = suggested_price - wholesale_price - delivery_fee
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
