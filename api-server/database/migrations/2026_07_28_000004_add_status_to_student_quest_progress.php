<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_quest_progress', function (Blueprint $table): void {
            $table->string('status', 32)
                ->default('not_started')
                ->after('quest_id')
                ->comment('not_started|in_progress|review_requested|rejected|completed');
        });

        DB::table('student_quest_progress')
            ->where('is_completed', true)
            ->update(['status' => 'completed']);
    }

    public function down(): void
    {
        Schema::table('student_quest_progress', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
