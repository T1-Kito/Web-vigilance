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
        if (!Schema::hasColumn('products', 'discount_percent')) {
            Schema::table('products', function (Blueprint $table) {
                $table->integer('discount_percent')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'discount_percent')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('discount_percent');
            });
        }
    }
};
