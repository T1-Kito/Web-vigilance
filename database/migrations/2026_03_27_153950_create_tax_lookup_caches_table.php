<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_lookup_caches', function (Blueprint $table) {
            $table->id();
            $table->string('tax_code', 32)->unique();
            $table->json('payload');
            $table->string('source_url')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_lookup_caches');
    }
};
