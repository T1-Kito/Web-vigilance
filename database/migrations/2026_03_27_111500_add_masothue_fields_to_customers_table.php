<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->text('tax_address')->nullable()->after('tax_id');
            $table->string('company_status')->nullable()->after('tax_address');
            $table->string('representative')->nullable()->after('company_status');
            $table->string('managed_by')->nullable()->after('representative');
            $table->date('active_date')->nullable()->after('managed_by');
            $table->string('business_type')->nullable()->after('active_date');
            $table->text('main_business')->nullable()->after('business_type');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'tax_address',
                'company_status',
                'representative',
                'managed_by',
                'active_date',
                'business_type',
                'main_business',
            ]);
        });
    }
};
