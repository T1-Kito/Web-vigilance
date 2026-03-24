<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_request_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('borrow_request_id');

            $table->unsignedInteger('line_no')->default(1);
            $table->string('item_name')->nullable();
            $table->string('unit')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('value', 18, 2)->nullable();
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index(['borrow_request_id', 'line_no']);
            $table->foreign('borrow_request_id')->references('id')->on('borrow_requests')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_request_items');
    }
};
