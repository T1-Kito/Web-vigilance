<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deliveries') && !Schema::hasColumn('deliveries', 'sales_order_id')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->after('order_id');
                $table->index('sales_order_id');
            });
        }

        if (Schema::hasTable('invoices') && !Schema::hasColumn('invoices', 'sales_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_order_id')->nullable()->after('order_id');
                $table->index('sales_order_id');
            });
        }

        if (Schema::hasTable('delivery_items') && !Schema::hasColumn('delivery_items', 'sales_order_item_id')) {
            Schema::table('delivery_items', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_order_item_id')->nullable()->after('order_item_id');
                $table->index('sales_order_item_id');
            });
        }

        if (Schema::hasTable('invoice_items') && !Schema::hasColumn('invoice_items', 'sales_order_item_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_order_item_id')->nullable()->after('order_item_id');
                $table->index('sales_order_item_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'sales_order_item_id')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->dropIndex(['sales_order_item_id']);
                $table->dropColumn('sales_order_item_id');
            });
        }

        if (Schema::hasTable('delivery_items') && Schema::hasColumn('delivery_items', 'sales_order_item_id')) {
            Schema::table('delivery_items', function (Blueprint $table) {
                $table->dropIndex(['sales_order_item_id']);
                $table->dropColumn('sales_order_item_id');
            });
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'sales_order_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex(['sales_order_id']);
                $table->dropColumn('sales_order_id');
            });
        }

        if (Schema::hasTable('deliveries') && Schema::hasColumn('deliveries', 'sales_order_id')) {
            Schema::table('deliveries', function (Blueprint $table) {
                $table->dropIndex(['sales_order_id']);
                $table->dropColumn('sales_order_id');
            });
        }
    }
};
