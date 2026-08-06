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
            $table->string('submission_type', 20)->nullable()->after('submission_url');
            $table->text('submission_text')->nullable()->after('submission_type');
        });

        DB::table('student_quest_progress')
            ->whereNotNull('submission_url')
            ->where('submission_url', '!=', '')
            ->update(['submission_type' => 'link']);
    }

    public function down(): void
    {
        Schema::table('student_quest_progress', function (Blueprint $table): void {
            $table->dropColumn(['submission_type', 'submission_text']);
        });
    }
};
