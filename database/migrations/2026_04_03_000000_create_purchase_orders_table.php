<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('order_type', ['order', 'return'])->default('order');
            $table->string('supplier_code')->nullable();
            $table->string('supplier_name');
            $table->string('supplier_address')->nullable();
            $table->string('supplier_tax_code')->nullable();
            $table->string('supplier_contact_name')->nullable();
            $table->string('supplier_contact_phone')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_location')->nullable();
            $table->string('buyer_name')->nullable();
            $table->string('payment_currency')->default('VND');
            $table->string('po_number')->nullable();
            $table->text('debt_note')->nullable();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
