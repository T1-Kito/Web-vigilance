<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranty_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warranty_id')->constrained()->onDelete('cascade'); // Liên kết bảo hành
            $table->foreignId('warranty_claim_id')->nullable()->constrained()->onDelete('cascade'); // Liên kết yêu cầu bảo hành (nếu có)
            $table->string('status'); // Trạng thái
            $table->text('notes')->nullable(); // Ghi chú
            $table->string('changed_by')->nullable(); // Người thay đổi
            $table->timestamps();
            
            // Indexes
            $table->index(['warranty_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warranty_statuses');
    }
};
