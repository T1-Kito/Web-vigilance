<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('delivery_code')->unique();
            $table->string('status', 30)->default('draft'); // draft, confirmed, cancelled
            $table->timestamp('delivered_at')->nullable();
            $table->string('shipper_name')->nullable();
            $table->string('shipper_phone', 50)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
