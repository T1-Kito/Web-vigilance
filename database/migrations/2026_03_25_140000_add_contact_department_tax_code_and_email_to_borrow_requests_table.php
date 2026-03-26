<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('borrow_requests', 'department')) {
                $table->string('department')->nullable()->after('approved_by_name');
            }
            if (!Schema::hasColumn('borrow_requests', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('customer_name');
            }
            if (!Schema::hasColumn('borrow_requests', 'tax_code')) {
                $table->string('tax_code')->nullable()->after('contact_name');
            }
            if (!Schema::hasColumn('borrow_requests', 'email')) {
                $table->string('email')->nullable()->after('tax_code');
            }
            if (!Schema::hasColumn('borrow_requests', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_requests', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
            if (Schema::hasColumn('borrow_requests', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('borrow_requests', 'tax_code')) {
                $table->dropColumn('tax_code');
            }
            if (Schema::hasColumn('borrow_requests', 'contact_name')) {
                $table->dropColumn('contact_name');
            }
            if (Schema::hasColumn('borrow_requests', 'department')) {
                $table->dropColumn('department');
            }
        });
    }
};
