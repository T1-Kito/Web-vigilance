<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('document_templates')) {
            return;
        }

        if (!Schema::hasColumn('document_templates', 'file_type')) {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->string('file_type', 20)->default('docx')->after('file_path');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('document_templates')) {
            return;
        }

        if (Schema::hasColumn('document_templates', 'file_type')) {
            Schema::table('document_templates', function (Blueprint $table) {
                $table->dropColumn('file_type');
            });
        }
    }
};
