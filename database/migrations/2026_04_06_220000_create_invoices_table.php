<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->string('invoice_code')->unique();
            $table->string('status', 30)->default('issued');
            $table->dateTime('issued_at')->nullable();

            $table->decimal('vat_percent', 5, 2)->default(8);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('sub_total', 14, 2)->default(0);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['order_id', 'issued_at']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
