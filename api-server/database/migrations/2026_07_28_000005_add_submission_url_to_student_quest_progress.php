<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_quest_progress', function (Blueprint $table): void {
            $table->string('submission_url', 2048)
                ->nullable()
                ->after('status')
                ->comment('提出物リンクURL');
        });
    }

    public function down(): void
    {
        Schema::table('student_quest_progress', function (Blueprint $table): void {
            $table->dropColumn('submission_url');
        });
    }
};
