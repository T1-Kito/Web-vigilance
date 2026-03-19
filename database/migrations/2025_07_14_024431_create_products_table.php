<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('discount_percent')->nullable(); // Phần trăm giảm giá admin nhập
            $table->text('description')->nullable();
            $table->text('specifications')->nullable(); // Thông số kỹ thuật
            $table->boolean('is_featured')->default(0); // Sản phẩm nổi bật
            $table->boolean('status')->default(1); // 1: hiển thị, 0: ẩn
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
