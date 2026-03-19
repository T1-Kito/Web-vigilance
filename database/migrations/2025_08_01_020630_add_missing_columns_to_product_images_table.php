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
        Schema::table('product_images', function (Blueprint $table) {
            if (!Schema::hasColumn('product_images', 'product_id')) {
                $table->unsignedBigInteger('product_id')->after('id');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            }
            if (!Schema::hasColumn('product_images', 'image_path')) {
                $table->string('image_path')->after('product_id');
            }
            if (!Schema::hasColumn('product_images', 'alt_text')) {
                $table->string('alt_text')->nullable()->after('image_path');
            }
            if (!Schema::hasColumn('product_images', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('alt_text');
            }
            if (!Schema::hasColumn('product_images', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('sort_order');
            }
            // Bỏ tạo index để tránh lỗi môi trường thiếu Doctrine; không bắt buộc cho chức năng hiện tại
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (Schema::hasColumn('product_images', 'product_id')) {
                $table->dropForeign(['product_id']);
            }
            // Không drop index vì không tạo ở up()
            $drop = [];
            foreach (['product_id','image_path','alt_text','sort_order','is_primary'] as $col) {
                if (Schema::hasColumn('product_images', $col)) { $drop[] = $col; }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
