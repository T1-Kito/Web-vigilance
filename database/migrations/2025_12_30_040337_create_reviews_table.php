<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating')->default(5); // 1-5 sao
            $table->tinyInteger('performance_rating')->nullable(); // Hiệu suất 1-5
            $table->tinyInteger('durability_rating')->nullable(); // Độ bền 1-5
            $table->text('content')->nullable(); // Nội dung đánh giá
            $table->string('images')->nullable(); // JSON array ảnh đánh giá
            $table->boolean('is_purchased')->default(false); // Đã mua hàng
            $table->boolean('is_approved')->default(true); // Duyệt hiển thị
            $table->timestamps();
            
            $table->unique(['product_id', 'user_id']); // Mỗi user chỉ đánh giá 1 lần/SP
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
