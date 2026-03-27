<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_question_events', function (Blueprint $table) {
            $table->boolean('is_unanswered')->default(false)->after('intent');
            $table->string('unanswered_reason', 80)->nullable()->after('is_unanswered');

            $table->index(['is_unanswered', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('chat_question_events', function (Blueprint $table) {
            $table->dropIndex(['is_unanswered', 'created_at']);
            $table->dropColumn(['is_unanswered', 'unanswered_reason']);
        });
    }
};
