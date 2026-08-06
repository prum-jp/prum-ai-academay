<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->unsignedInteger('experience_points')
                ->default(0)
                ->after('difficulty');
        });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->dropColumn('experience_points');
        });
    }
};
