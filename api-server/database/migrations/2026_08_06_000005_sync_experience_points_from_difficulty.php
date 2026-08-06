<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quests')
            ->whereNotNull('difficulty')
            ->update([
                'experience_points' => DB::raw('difficulty * 4'),
            ]);
    }

    public function down(): void
    {
        // 旧手入力値は復元できないため no-op
    }
};
