<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('quest_rewards')->delete();
        DB::table('quest_unit_rewards')->delete();
    }

    public function down(): void
    {
        // 旧6ステータス報酬は復元不可
    }
};
