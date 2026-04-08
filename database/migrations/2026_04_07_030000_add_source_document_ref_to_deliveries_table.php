<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'source_document_ref')) {
                $table->string('source_document_ref', 255)->nullable()->after('delivery_location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'source_document_ref')) {
                $table->dropColumn('source_document_ref');
            }
        });
    }
};
