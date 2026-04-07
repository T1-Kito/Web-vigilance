<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (!Schema::hasColumn('deliveries', 'receiver_name')) {
                $table->string('receiver_name')->nullable()->after('shipper_phone');
            }
            if (!Schema::hasColumn('deliveries', 'receiver_address')) {
                $table->string('receiver_address', 2000)->nullable()->after('receiver_name');
            }
            if (!Schema::hasColumn('deliveries', 'delivery_reason')) {
                $table->string('delivery_reason', 2000)->nullable()->after('receiver_address');
            }
            if (!Schema::hasColumn('deliveries', 'delivery_location')) {
                $table->string('delivery_location', 2000)->nullable()->after('delivery_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliveries', function (Blueprint $table) {
            if (Schema::hasColumn('deliveries', 'delivery_location')) {
                $table->dropColumn('delivery_location');
            }
            if (Schema::hasColumn('deliveries', 'delivery_reason')) {
                $table->dropColumn('delivery_reason');
            }
            if (Schema::hasColumn('deliveries', 'receiver_address')) {
                $table->dropColumn('receiver_address');
            }
            if (Schema::hasColumn('deliveries', 'receiver_name')) {
                $table->dropColumn('receiver_name');
            }
        });
    }
};
