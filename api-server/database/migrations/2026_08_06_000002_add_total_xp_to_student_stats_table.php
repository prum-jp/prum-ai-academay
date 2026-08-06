<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_stats', function (Blueprint $table): void {
            $table->unsignedInteger('total_xp')
                ->default(0)
                ->after('stat_support');
        });

        DB::table('student_stats')->update([
            'total_xp' => DB::raw(
                '(SELECT COALESCE(SUM(q.experience_points), 0)
                  FROM student_quest_progress sqp
                  INNER JOIN quests q ON q.id = sqp.quest_id
                  WHERE sqp.user_id = student_stats.user_id
                    AND sqp.is_completed = 1)',
            ),
        ]);
    }

    public function down(): void
    {
        Schema::table('student_stats', function (Blueprint $table): void {
            $table->dropColumn('total_xp');
        });
    }
};
