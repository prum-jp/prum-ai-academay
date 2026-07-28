<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('quest_submission_comments');
        Schema::dropIfExists('quest_submissions');

        if (Schema::hasColumn('quests', 'requires_submission')) {
            Schema::table('quests', function (Blueprint $table): void {
                $table->dropColumn('requires_submission');
            });
        }
    }

    public function down(): void
    {
        // 提出機能は後回しのためロールバックは不要
    }
};
