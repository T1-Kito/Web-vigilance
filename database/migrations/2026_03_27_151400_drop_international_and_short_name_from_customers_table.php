<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'international_name')) {
                $table->dropColumn('international_name');
            }
            if (Schema::hasColumn('customers', 'short_name')) {
                $table->dropColumn('short_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'international_name')) {
                $table->string('international_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('customers', 'short_name')) {
                $table->string('short_name')->nullable()->after('international_name');
            }
        });
    }
};
