<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_tax_code')) {
                $table->string('customer_tax_code', 50)->nullable()->after('note');
            }
            if (!Schema::hasColumn('orders', 'invoice_company_name')) {
                $table->string('invoice_company_name')->nullable()->after('customer_tax_code');
            }
            if (!Schema::hasColumn('orders', 'invoice_address')) {
                $table->string('invoice_address')->nullable()->after('invoice_company_name');
            }
            if (!Schema::hasColumn('orders', 'customer_email')) {
                $table->string('customer_email')->nullable()->after('invoice_address');
            }
            if (!Schema::hasColumn('orders', 'customer_phone')) {
                $table->string('customer_phone', 50)->nullable()->after('customer_email');
            }
            if (!Schema::hasColumn('orders', 'customer_contact_person')) {
                $table->string('customer_contact_person', 100)->nullable()->after('customer_phone');
            }
            if (!Schema::hasColumn('orders', 'payment_term')) {
                $table->string('payment_term', 30)->nullable()->after('customer_contact_person');
            }
            if (!Schema::hasColumn('orders', 'payment_due_days')) {
                $table->integer('payment_due_days')->nullable()->after('payment_term');
            }
            if (!Schema::hasColumn('orders', 'deposit_percent')) {
                $table->decimal('deposit_percent', 5, 2)->nullable()->after('payment_due_days');
            }
            if (!Schema::hasColumn('orders', 'payment_note')) {
                $table->string('payment_note', 500)->nullable()->after('deposit_percent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['payment_note','deposit_percent','payment_due_days','payment_term','customer_contact_person','customer_phone','customer_email','invoice_address','invoice_company_name','customer_tax_code'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
