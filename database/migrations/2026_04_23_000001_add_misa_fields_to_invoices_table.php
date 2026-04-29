<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('misa_ref_id')->nullable()->after('note');
            $table->string('misa_transaction_id')->nullable()->after('misa_ref_id');
            $table->string('misa_inv_series')->nullable()->after('misa_transaction_id');
            $table->string('misa_invoice_code')->nullable()->after('misa_inv_series');
            $table->json('misa_request_payload')->nullable()->after('misa_invoice_code');
            $table->json('misa_response_payload')->nullable()->after('misa_request_payload');
            $table->text('misa_error_message')->nullable()->after('misa_response_payload');
            $table->timestamp('misa_issued_at')->nullable()->after('misa_error_message');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'misa_ref_id',
                'misa_transaction_id',
                'misa_inv_series',
                'misa_invoice_code',
                'misa_request_payload',
                'misa_response_payload',
                'misa_error_message',
                'misa_issued_at',
            ]);
        });
    }
};
