<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            if (!Schema::hasColumn('quotes', 'payment_term')) {
                $table->string('payment_term', 30)->default('full_advance')->after('vat_percent');
            }
            if (!Schema::hasColumn('quotes', 'payment_due_days')) {
                $table->unsignedInteger('payment_due_days')->nullable()->after('payment_term');
            }
            if (!Schema::hasColumn('quotes', 'deposit_percent')) {
                $table->decimal('deposit_percent', 5, 2)->nullable()->after('payment_due_days');
            }
            if (!Schema::hasColumn('quotes', 'payment_note')) {
                $table->string('payment_note', 500)->nullable()->after('deposit_percent');
            }
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('sales_orders', 'payment_term')) {
                $table->string('payment_term', 30)->default('full_advance')->after('vat_percent');
            }
            if (!Schema::hasColumn('sales_orders', 'payment_due_days')) {
                $table->unsignedInteger('payment_due_days')->nullable()->after('payment_term');
            }
            if (!Schema::hasColumn('sales_orders', 'deposit_percent')) {
                $table->decimal('deposit_percent', 5, 2)->nullable()->after('payment_due_days');
            }
            if (!Schema::hasColumn('sales_orders', 'payment_note')) {
                $table->string('payment_note', 500)->nullable()->after('deposit_percent');
            }
            if (!Schema::hasColumn('sales_orders', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_note');
            }
            if (!Schema::hasColumn('sales_orders', 'payment_status')) {
                $table->string('payment_status', 20)->default('unpaid')->after('paid_amount');
            }
            if (!Schema::hasColumn('sales_orders', 'payment_due_date')) {
                $table->date('payment_due_date')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('sales_orders', 'delivery_due_date')) {
                $table->date('delivery_due_date')->nullable()->after('payment_due_date');
            }
            if (!Schema::hasColumn('sales_orders', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('delivery_due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            foreach (['paid_at', 'payment_due_date', 'payment_status', 'paid_amount', 'payment_note', 'deposit_percent', 'payment_due_days', 'payment_term'] as $col) {
                if (Schema::hasColumn('sales_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('quotes', function (Blueprint $table) {
            foreach (['payment_note', 'deposit_percent', 'payment_due_days', 'payment_term'] as $col) {
                if (Schema::hasColumn('quotes', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
