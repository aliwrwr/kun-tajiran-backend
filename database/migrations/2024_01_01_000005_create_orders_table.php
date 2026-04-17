<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // KT-2024-00001
            $table->foreignId('reseller_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('delivery_agent_id')->nullable()->constrained('delivery_agents')->nullOnDelete();

            // Customer Info
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_city');
            $table->text('customer_address');

            // Financials
            $table->unsignedBigInteger('total_sale_price');      // ما دفعه الزبون
            $table->unsignedBigInteger('total_wholesale_price'); // تكلفتنا
            $table->unsignedBigInteger('delivery_fee');
            $table->unsignedBigInteger('reseller_profit');       // ربح البائع
            $table->unsignedBigInteger('platform_profit');       // ربحنا

            // Status
            $table->enum('status', [
                'new',          // جديد
                'confirmed',    // مؤكد
                'preparing',    // قيد التجهيز
                'out_for_delivery', // قيد التوصيل
                'delivered',    // تم التسليم
                'rejected',     // مرفوض
                'returned',     // مرتجع
            ])->default('new');

            $table->enum('payment_method', ['cod', 'zain_cash', 'asia_hawala'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('rejection_reason')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['reseller_id', 'status']);
            $table->index('delivery_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
