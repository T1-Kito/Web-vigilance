<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quote_items', 'vat_percent')) {
                $table->decimal('vat_percent', 5, 2)->default(8)->after('unit');
            }
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_order_items', 'vat_percent')) {
                $table->decimal('vat_percent', 5, 2)->default(8)->after('unit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quote_items', function (Blueprint $table) {
            if (Schema::hasColumn('quote_items', 'vat_percent')) {
                $table->dropColumn('vat_percent');
            }
        });

        Schema::table('sales_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('sales_order_items', 'vat_percent')) {
                $table->dropColumn('vat_percent');
            }
        });
    }
};
