<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'source_quote_id')) {
                $table->unsignedBigInteger('source_quote_id')->nullable()->after('user_id');
                $table->index('source_quote_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'source_quote_id')) {
                $table->dropIndex(['source_quote_id']);
                $table->dropColumn('source_quote_id');
            }
        });
    }
};
