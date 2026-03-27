<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->string('approved_by_name')->nullable()->after('requested_by_name');
            $table->decimal('deposit_amount', 15, 2)->nullable()->after('deposit_text');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_requests', function (Blueprint $table) {
            $table->dropColumn(['approved_by_name', 'deposit_amount']);
        });
    }
};
