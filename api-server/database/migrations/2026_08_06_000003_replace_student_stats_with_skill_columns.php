<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_stats', function (Blueprint $table): void {
            $table->unsignedInteger('stat_business_skill')->default(0)->after('user_id');
            $table->unsignedInteger('stat_human_skill')->default(0)->after('stat_business_skill');
            $table->unsignedInteger('stat_conceptual_skill')->default(0)->after('stat_human_skill');
        });

        Schema::table('student_stats', function (Blueprint $table): void {
            $table->dropColumn([
                'stat_presentation',
                'stat_communication',
                'stat_problem_finding',
                'stat_ai_affinity',
                'stat_action',
                'stat_support',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('student_stats', function (Blueprint $table): void {
            $table->integer('stat_presentation')->default(0);
            $table->integer('stat_communication')->default(0);
            $table->integer('stat_problem_finding')->default(0);
            $table->integer('stat_ai_affinity')->default(0);
            $table->integer('stat_action')->default(0);
            $table->integer('stat_support')->default(0);
        });

        Schema::table('student_stats', function (Blueprint $table): void {
            $table->dropColumn([
                'stat_business_skill',
                'stat_human_skill',
                'stat_conceptual_skill',
            ]);
        });
    }
};
