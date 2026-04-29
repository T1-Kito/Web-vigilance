<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sales_orders')) {
            return;
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'source_order_id')) {
                $table->unsignedBigInteger('source_order_id')->nullable()->after('source_quote_id');
                $table->index('source_order_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('sales_orders')) {
            return;
        }

        Schema::table('sales_orders', function (Blueprint $table) {
            if (Schema::hasColumn('sales_orders', 'source_order_id')) {
                $table->dropIndex(['source_order_id']);
                $table->dropColumn('source_order_id');
            }
        });
    }
};
