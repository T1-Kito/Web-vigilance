<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade'); // Liên kết bảo hành
            $table->string('claim_number')->unique(); // Số yêu cầu bảo hành
            $table->date('claim_date'); // Ngày yêu cầu bảo hành
            $table->text('issue_description'); // Mô tả lỗi
            $table->text('customer_complaint'); // Khiếu nại của khách hàng
            $table->enum('claim_status', ['pending', 'approved', 'rejected', 'in_progress', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable(); // Ghi chú của admin
            $table->text('repair_notes')->nullable(); // Ghi chú sửa chữa
            $table->date('estimated_completion_date')->nullable(); // Ngày dự kiến hoàn thành
            $table->date('actual_completion_date')->nullable(); // Ngày hoàn thành thực tế
            $table->decimal('repair_cost', 15, 2)->nullable(); // Chi phí sửa chữa
            $table->string('technician_name')->nullable(); // Tên kỹ thuật viên
            $table->timestamps();
            
            // Indexes
            $table->index(['claim_number', 'claim_status']);
            $table->index('claim_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_claims');
    }
};
