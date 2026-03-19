<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repair_forms', function (Blueprint $table) {
            $table->string('service_representative', 255)->nullable()->after('received_by_phone');
        });
    }

    public function down(): void
    {
        Schema::table('repair_forms', function (Blueprint $table) {
            $table->dropColumn('service_representative');
        });
    }
};
