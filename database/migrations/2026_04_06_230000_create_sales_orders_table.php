<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('source_quote_id')->nullable();
            $table->string('sales_order_code')->unique();

            $table->string('receiver_name');
            $table->string('receiver_phone', 50);
            $table->string('receiver_address', 2000);

            $table->string('invoice_company_name')->nullable();
            $table->string('invoice_address', 2000)->nullable();
            $table->string('customer_tax_code', 50)->nullable();
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_contact_person', 100)->nullable();

            $table->string('staff_code', 100)->nullable();
            $table->string('sales_name', 150)->nullable();
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('vat_percent', 5, 2)->default(8);
            $table->string('status', 30)->default('pending');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('source_quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
