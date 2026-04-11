<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('from_qty');
            $table->unsignedInteger('to_qty')->nullable();
            $table->string('customer_type', 30)->default('all'); // all|retail|agent|factory|enterprise
            $table->string('pricing_type', 30); // fixed | percent_discount
            $table->decimal('price_value', 15, 2)->nullable();
            $table->decimal('percent_value', 8, 2)->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['product_id', 'is_active']);
            $table->index(['product_id', 'from_qty', 'to_qty']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_tiers');
    }
};
