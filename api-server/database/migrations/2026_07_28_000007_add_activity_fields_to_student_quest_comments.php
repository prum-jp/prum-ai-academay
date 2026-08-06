<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_quest_comments', function (Blueprint $table): void {
            $table->string('type', 32)
                ->default('comment')
                ->after('author_id')
                ->comment('comment|status_changed|submission_added');
            $table->json('metadata')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('student_quest_comments', function (Blueprint $table): void {
            $table->dropColumn(['type', 'metadata']);
        });
    }
};
