<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('vehicle_type')->nullable(); // دراجة / سيارة
            $table->string('license_number')->nullable();
            $table->string('city');
            $table->enum('status', ['available', 'busy', 'offline'])->default('available');
            $table->unsignedInteger('delivered_count')->default(0);
            $table->unsignedBigInteger('total_earnings')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_agents');
    }
};
