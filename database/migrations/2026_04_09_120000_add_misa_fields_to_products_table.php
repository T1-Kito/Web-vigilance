<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('unit_name', 100)->nullable()->after('brand');
            $table->string('origin', 150)->nullable()->after('unit_name');
            $table->string('default_warehouse', 150)->nullable()->after('origin');

            $table->decimal('factory_price', 15, 2)->nullable()->after('price');
            $table->decimal('agency_suggested_price', 15, 2)->nullable()->after('factory_price');
            $table->decimal('agency_price', 15, 2)->nullable()->after('agency_suggested_price');
            $table->decimal('retail_price', 15, 2)->nullable()->after('agency_price');
            $table->decimal('shipping_price', 15, 2)->nullable()->after('retail_price');
            $table->decimal('labor_price', 15, 2)->nullable()->after('shipping_price');
            $table->decimal('vat_percent', 8, 2)->nullable()->after('labor_price');
            $table->boolean('price_includes_tax')->default(false)->after('vat_percent');
            $table->string('default_revenue_mode', 100)->nullable()->after('price_includes_tax');
            $table->decimal('cost_price', 15, 2)->nullable()->after('default_revenue_mode');

            $table->unsignedInteger('warranty_months')->nullable()->after('instruction');
            $table->text('warranty_content')->nullable()->after('warranty_months');

            $table->decimal('height', 12, 2)->nullable()->after('warranty_content');
            $table->decimal('length', 12, 2)->nullable()->after('height');
            $table->decimal('width', 12, 2)->nullable()->after('length');
            $table->decimal('radius', 12, 2)->nullable()->after('width');
            $table->decimal('weight', 12, 2)->nullable()->after('radius');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'unit_name', 'origin', 'default_warehouse',
                'factory_price', 'agency_suggested_price', 'agency_price', 'retail_price',
                'shipping_price', 'labor_price', 'vat_percent', 'price_includes_tax',
                'default_revenue_mode', 'cost_price',
                'warranty_months', 'warranty_content',
                'height', 'length', 'width', 'radius', 'weight',
            ]);
        });
    }
};
