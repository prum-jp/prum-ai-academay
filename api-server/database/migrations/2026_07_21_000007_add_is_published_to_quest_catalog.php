<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quest_units', function (Blueprint $table): void {
            $table->boolean('is_published')->default(false)->after('sort_order');
        });

        Schema::table('quests', function (Blueprint $table): void {
            $table->boolean('is_published')->default(false)->after('sort_order');
        });

        // 既存データは公開済み扱いにして、学生側の表示を維持する。
        DB::table('quest_units')->update(['is_published' => true]);
        DB::table('quests')->update(['is_published' => true]);
    }

    public function down(): void
    {
        Schema::table('quest_units', function (Blueprint $table): void {
            $table->dropColumn('is_published');
        });

        Schema::table('quests', function (Blueprint $table): void {
            $table->dropColumn('is_published');
        });
    }
};
