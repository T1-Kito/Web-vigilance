<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('source_invoice_id')->nullable()->after('sales_order_id');
            $table->string('reference_action', 20)->nullable()->after('status'); // replacement|adjustment
            $table->text('reference_reason')->nullable()->after('note');
            $table->string('misa_publish_view_url')->nullable()->after('misa_invoice_code');

            $table->index('source_invoice_id');
            $table->index('reference_action');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['source_invoice_id']);
            $table->dropIndex(['reference_action']);
            $table->dropColumn([
                'source_invoice_id',
                'reference_action',
                'reference_reason',
                'misa_publish_view_url',
            ]);
        });
    }
};
