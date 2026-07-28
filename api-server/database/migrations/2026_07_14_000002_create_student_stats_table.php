<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->integer('stat_presentation')->default(0)->comment('プレゼン力');
            $table->integer('stat_communication')->default(0)->comment('コミュニケーション');
            $table->integer('stat_problem_finding')->default(0)->comment('課題発見力');
            $table->integer('stat_ai_affinity')->default(0)->comment('AI親和性');
            $table->integer('stat_action')->default(0)->comment('行動力');
            $table->integer('stat_support')->default(0)->comment('サポート精神');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_stats');
    }
};
