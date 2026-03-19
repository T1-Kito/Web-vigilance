<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->boolean('is_addon')->default(false)->after('sale');
            $table->unsignedBigInteger('parent_cart_item_id')->nullable()->after('is_addon');
            $table->unsignedBigInteger('addon_product_id')->nullable()->after('parent_cart_item_id');
            $table->foreign('parent_cart_item_id')->references('id')->on('cart_items')->onDelete('cascade');
            $table->foreign('addon_product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['parent_cart_item_id']);
            $table->dropForeign(['addon_product_id']);
            $table->dropColumn(['is_addon', 'parent_cart_item_id', 'addon_product_id']);
        });
    }
}; 