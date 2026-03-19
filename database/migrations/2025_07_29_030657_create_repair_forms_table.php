<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repair_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade');
            $table->foreignId('warranty_claim_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('form_number', 50)->unique(); // Số phiếu
            $table->string('customer_company', 255); // Tên công ty
            $table->string('contact_person', 255); // Người liên hệ
            $table->string('contact_phone', 20); // Số điện thoại
            $table->string('alternate_contact', 255)->nullable(); // Người liên hệ dự phòng
            $table->string('alternate_phone', 20)->nullable(); // Số điện thoại dự phòng
            $table->date('purchase_date'); // Ngày mua
            $table->string('company_phone', 20)->nullable(); // Điện thoại công ty
            $table->string('fax', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('equipment_name', 255); // Tên thiết bị
            $table->text('error_status'); // Tình trạng lỗi
            $table->text('serial_numbers'); // Số serial (có thể nhiều)
            $table->enum('warranty_status', ['under_warranty', 'out_of_warranty']); // Trạng thái bảo hành
            $table->integer('employee_count')->nullable(); // Số nhân viên sử dụng
            $table->string('repair_time_required', 100); // Thời gian sửa chữa cần
            $table->date('estimated_return_date')->nullable(); // Ngày trả dự kiến
            $table->string('received_by', 255); // Người tiếp nhận
            $table->date('received_date'); // Ngày tiếp nhận
            $table->text('notes')->nullable(); // Ghi chú
            $table->enum('status', ['draft', 'submitted', 'in_progress', 'completed'])->default('draft');
            $table->timestamps();
            
            // Indexes
            $table->index(['form_number']);
            $table->index(['warranty_id']);
            $table->index(['status']);
            $table->index(['received_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_forms');
    }
};
