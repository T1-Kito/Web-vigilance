<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();

            $table->unsignedBigInteger('requested_by_admin_id')->nullable();

            $table->string('customer_name')->nullable();
            $table->string('purpose')->nullable();
            $table->string('current_project')->nullable();

            $table->date('borrow_from')->nullable();
            $table->date('borrow_to')->nullable();

            $table->string('deposit_text')->nullable();

            $table->string('status')->default('proposed');
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['status', 'borrow_from']);
            $table->foreign('requested_by_admin_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('borrow_requests');
    }
};
