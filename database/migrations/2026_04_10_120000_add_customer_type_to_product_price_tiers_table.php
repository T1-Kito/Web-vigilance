<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('product_price_tiers')) {
            return;
        }

        Schema::table('product_price_tiers', function (Blueprint $table) {
            if (!Schema::hasColumn('product_price_tiers', 'customer_type')) {
                $table->string('customer_type', 30)
                    ->default('all')
                    ->after('to_qty');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_price_tiers')) {
            return;
        }

        Schema::table('product_price_tiers', function (Blueprint $table) {
            if (Schema::hasColumn('product_price_tiers', 'customer_type')) {
                $table->dropColumn('customer_type');
            }
        });
    }
};
