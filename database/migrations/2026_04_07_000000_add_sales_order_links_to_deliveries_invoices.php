<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'sales_order_id')) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->after('order_id');
                $table->index('sales_order_id');
            }
        });

        Schema::table('delivery_items', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_items', 'sales_order_item_id')) {
                $table->unsignedBigInteger('sales_order_item_id')->nullable()->after('order_item_id');
                $table->index('sales_order_item_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'sales_order_id')) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->after('order_id');
                $table->index('sales_order_id');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'sales_order_item_id')) {
                $table->unsignedBigInteger('sales_order_item_id')->nullable()->after('order_item_id');
                $table->index('sales_order_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            if (Schema::hasColumn('invoice_items', 'sales_order_item_id')) {
                $table->dropIndex(['sales_order_item_id']);
                $table->dropColumn('sales_order_item_id');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'sales_order_id')) {
                $table->dropIndex(['sales_order_id']);
                $table->dropColumn('sales_order_id');
            }
        });

        Schema::table('delivery_items', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_items', 'sales_order_item_id')) {
                $table->dropIndex(['sales_order_item_id']);
                $table->dropColumn('sales_order_item_id');
            }
        });

        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'sales_order_id')) {
                $table->dropIndex(['sales_order_id']);
                $table->dropColumn('sales_order_id');
            }
        });
    }
};
