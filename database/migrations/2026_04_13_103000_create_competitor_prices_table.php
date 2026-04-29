<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitor_prices', function (Blueprint $table) {
            $table->id();
            $table->string('competitor_name', 150);
            $table->string('product_key', 255);
            $table->string('product_name_raw', 255)->nullable();
            $table->decimal('price', 15, 2);
            $table->string('product_url', 1000)->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['product_key', 'checked_at']);
            $table->index(['competitor_name', 'product_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_prices');
    }
};
