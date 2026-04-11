<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quotes')) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'customer_type')) {
                $table->string('customer_type', 30)->nullable()->after('customer_contact_person');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('quotes')) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) {
            if (Schema::hasColumn('quotes', 'customer_type')) {
                $table->dropColumn('customer_type');
            }
        });
    }
};
