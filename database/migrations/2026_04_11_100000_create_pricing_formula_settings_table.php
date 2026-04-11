<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_formula_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('list_multiplier', 10, 4)->default(2.0);
            $table->decimal('retail_discount_percent', 6, 2)->default(15.0);
            $table->decimal('agent_markup_1_5_percent', 6, 2)->default(30.0);
            $table->decimal('agent_markup_6_10_percent', 6, 2)->default(25.0);
            $table->decimal('agent_markup_over_10_percent', 6, 2)->default(15.0);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_formula_settings');
    }
};
