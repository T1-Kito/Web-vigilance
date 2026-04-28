<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_forms', 'return_file_path')) {
                $table->string('return_file_path')->nullable()->after('status');
            }
            if (!Schema::hasColumn('repair_forms', 'return_file_original_name')) {
                $table->string('return_file_original_name')->nullable()->after('return_file_path');
            }
            if (!Schema::hasColumn('repair_forms', 'return_file_uploaded_at')) {
                $table->timestamp('return_file_uploaded_at')->nullable()->after('return_file_original_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_forms', function (Blueprint $table) {
            if (Schema::hasColumn('repair_forms', 'return_file_uploaded_at')) {
                $table->dropColumn('return_file_uploaded_at');
            }
            if (Schema::hasColumn('repair_forms', 'return_file_original_name')) {
                $table->dropColumn('return_file_original_name');
            }
            if (Schema::hasColumn('repair_forms', 'return_file_path')) {
                $table->dropColumn('return_file_path');
            }
        });
    }
};
