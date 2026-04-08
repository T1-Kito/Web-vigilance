<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('deliveries') && Schema::hasColumn('deliveries', 'order_id')) {
            DB::statement('ALTER TABLE deliveries MODIFY order_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('delivery_items') && Schema::hasColumn('delivery_items', 'order_item_id')) {
            DB::statement('ALTER TABLE delivery_items MODIFY order_item_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'order_id')) {
            DB::statement('ALTER TABLE invoices MODIFY order_id BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'order_item_id')) {
            DB::statement('ALTER TABLE invoice_items MODIFY order_item_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoice_items') && Schema::hasColumn('invoice_items', 'order_item_id')) {
            DB::statement('ALTER TABLE invoice_items MODIFY order_item_id BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'order_id')) {
            DB::statement('ALTER TABLE invoices MODIFY order_id BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasTable('delivery_items') && Schema::hasColumn('delivery_items', 'order_item_id')) {
            DB::statement('ALTER TABLE delivery_items MODIFY order_item_id BIGINT UNSIGNED NOT NULL');
        }

        if (Schema::hasTable('deliveries') && Schema::hasColumn('deliveries', 'order_id')) {
            DB::statement('ALTER TABLE deliveries MODIFY order_id BIGINT UNSIGNED NOT NULL');
        }
    }
};
