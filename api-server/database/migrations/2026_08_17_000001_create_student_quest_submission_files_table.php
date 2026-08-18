<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_quest_submission_files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_quest_progress_id')
                ->constrained('student_quest_progress')
                ->cascadeOnDelete();
            $table->string('storage_path', 2048);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('student_quest_progress_id');
        });

        $legacyImages = DB::table('student_quest_progress')
            ->where('submission_type', 'image')
            ->whereNotNull('submission_url')
            ->where('submission_url', '!=', '')
            ->orderBy('id')
            ->get(['id', 'submission_url']);

        foreach ($legacyImages as $row) {
            DB::table('student_quest_submission_files')->insert([
                'student_quest_progress_id' => $row->id,
                'storage_path' => $row->submission_url,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('student_quest_progress')
            ->where('submission_type', 'image')
            ->update(['submission_url' => null]);
    }

    public function down(): void
    {
        Schema::dropIfExists('student_quest_submission_files');
    }
};
