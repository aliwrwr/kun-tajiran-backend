<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('balance')->default(0);        // الرصيد المتاح
            $table->unsignedBigInteger('pending_balance')->default(0); // قيد التحرير
            $table->unsignedBigInteger('total_earned')->default(0);   // إجمالي الأرباح
            $table->unsignedBigInteger('total_withdrawn')->default(0);// إجمالي المسحوب
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['credit', 'debit']); // إضافة / خصم
            $table->enum('category', [
                'order_profit',    // ربح طلب
                'withdrawal',      // سحب
                'refund',          // استرداد
                'bonus',           // مكافأة
                'penalty',         // خصم
            ]);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('balance_after');
            $table->string('description');
            $table->string('reference')->nullable(); // رقم مرجعي
            $table->timestamps();

            $table->index('wallet_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};
