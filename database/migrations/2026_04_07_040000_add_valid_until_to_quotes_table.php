<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'valid_until')) {
                $table->date('valid_until')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'valid_until')) {
                $table->dropColumn('valid_until');
            }
        });
    }
};
