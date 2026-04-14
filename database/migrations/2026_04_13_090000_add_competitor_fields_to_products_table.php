<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('competitor_source', 255)->nullable()->after('cost_price');
            $table->decimal('competitor_price', 15, 2)->nullable()->after('competitor_source');
            $table->string('competitor_product_url', 1000)->nullable()->after('competitor_price');
            $table->timestamp('competitor_checked_at')->nullable()->after('competitor_product_url');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'competitor_source',
                'competitor_price',
                'competitor_product_url',
                'competitor_checked_at',
            ]);
        });
    }
};
