<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quest_tool', function (Blueprint $table): void {
            $table->foreignId('quest_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(1);
            $table->primary(['quest_id', 'tool_id']);
        });

        $rows = DB::table('quests')
            ->whereNotNull('tool_id')
            ->get(['id', 'tool_id']);

        foreach ($rows as $row) {
            DB::table('quest_tool')->insert([
                'quest_id' => $row->id,
                'tool_id' => $row->tool_id,
                'sort_order' => 1,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quest_tool');
    }
};
