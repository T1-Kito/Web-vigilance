<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_question_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_id', 64)->nullable();
            $table->string('intent', 40)->default('unknown');
            $table->text('text');
            $table->string('normalized_text', 500);
            $table->string('page_url', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();

            $table->index(['created_at']);
            $table->index(['intent', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['guest_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_question_events');
    }
};
