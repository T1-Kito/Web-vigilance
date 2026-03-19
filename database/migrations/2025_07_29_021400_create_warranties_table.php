<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique(); // Số seri sản phẩm
            $table->foreignId('product_id')->constrained()->onDelete('cascade'); // Liên kết sản phẩm
            $table->string('customer_name'); // Tên khách hàng
            $table->string('customer_phone'); // SĐT khách hàng
            $table->string('customer_email')->nullable(); // Email khách hàng
            $table->text('customer_address')->nullable(); // Địa chỉ khách hàng
            $table->date('purchase_date'); // Ngày mua
            $table->date('warranty_start_date'); // Ngày bắt đầu bảo hành
            $table->date('warranty_end_date'); // Ngày kết thúc bảo hành
            $table->integer('warranty_period_months')->default(12); // Thời hạn bảo hành (tháng)
            $table->string('invoice_number')->nullable(); // Số hóa đơn
            $table->decimal('purchase_price', 15, 2)->nullable(); // Giá mua
            $table->text('notes')->nullable(); // Ghi chú
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active'); // Trạng thái bảo hành
            $table->timestamps();
            
            // Indexes
            $table->index(['serial_number', 'status']);
            $table->index('warranty_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranties');
    }
};
