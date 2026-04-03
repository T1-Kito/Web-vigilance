<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'tax_address')) {
                $table->text('tax_address')->nullable()->after('tax_id');
            }
            if (!Schema::hasColumn('customers', 'company_status')) {
                $table->string('company_status')->nullable()->after('tax_address');
            }
            if (!Schema::hasColumn('customers', 'representative')) {
                $table->string('representative')->nullable()->after('company_status');
            }
            if (!Schema::hasColumn('customers', 'managed_by')) {
                $table->string('managed_by')->nullable()->after('representative');
            }
            if (!Schema::hasColumn('customers', 'active_date')) {
                $table->date('active_date')->nullable()->after('managed_by');
            }
            if (!Schema::hasColumn('customers', 'business_type')) {
                $table->string('business_type')->nullable()->after('active_date');
            }
            if (!Schema::hasColumn('customers', 'main_business')) {
                $table->text('main_business')->nullable()->after('business_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $drop = [];
            foreach ([
                'tax_address',
                'company_status',
                'representative',
                'managed_by',
                'active_date',
                'business_type',
                'main_business',
            ] as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};

