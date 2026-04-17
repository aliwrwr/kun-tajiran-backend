<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->string('product_name'); // snapshot at order time
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('wholesale_price'); // snapshot
            $table->unsignedBigInteger('sale_price');      // سعر البيع الفعلي (قد يعدله البائع)
            $table->unsignedBigInteger('profit_per_item'); // ربح البائع لكل وحدة
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
