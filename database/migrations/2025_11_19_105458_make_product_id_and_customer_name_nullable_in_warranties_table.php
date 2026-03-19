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
        Schema::table('warranties', function (Blueprint $table) {
            // Xóa foreign key constraint trước
            $table->dropForeign(['product_id']);
            
            // Thay đổi product_id thành nullable
            $table->unsignedBigInteger('product_id')->nullable()->change();
            
            // Thêm lại foreign key constraint với nullable
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Thay đổi customer_name và customer_phone thành nullable
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_phone')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warranties', function (Blueprint $table) {
            // Xóa foreign key constraint
            $table->dropForeign(['product_id']);
            
            // Thay đổi lại thành NOT NULL
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            
            // Thêm lại foreign key constraint
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            
            // Thay đổi lại customer_name và customer_phone thành NOT NULL
            $table->string('customer_name')->nullable(false)->change();
            $table->string('customer_phone')->nullable(false)->change();
        });
    }
};
