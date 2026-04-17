<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('amount');
            $table->enum('method', ['zain_cash', 'asia_hawala', 'bank_transfer', 'office_pickup']);
            $table->string('account_number')->nullable(); // رقم المحفظة أو الحساب
            $table->string('account_name')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
    }
};
