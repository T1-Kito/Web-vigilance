<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chỉ rename nếu cột cũ tồn tại và cột mới chưa có
        if (Schema::hasColumn('products', 'specs') && !Schema::hasColumn('products', 'specifications')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('specs', 'specifications');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'specifications') && !Schema::hasColumn('products', 'specs')) {
            Schema::table('products', function (Blueprint $table) {
                $table->renameColumn('specifications', 'specs');
            });
        }
    }
}; 