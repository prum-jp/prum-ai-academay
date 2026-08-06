<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->unsignedTinyInteger('difficulty')
                ->nullable()
                ->after('estimated_duration')
                ->comment('難易度（1〜5）');
        });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->dropColumn('difficulty');
        });
    }
};
