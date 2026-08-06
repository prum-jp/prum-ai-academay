<?php

use App\Support\QuestTier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->string('quest_tier', 20)->nullable()->after('unlock_level');
        });

        DB::table('quests')
            ->whereNotNull('quest_unit_id')
            ->orderBy('id')
            ->chunkById(200, function ($quests): void {
                foreach ($quests as $quest) {
                    $unlockLevel = $quest->unlock_level !== null ? (int) $quest->unlock_level : null;
                    $tier = QuestTier::fromUnlockLevel($unlockLevel);

                    DB::table('quests')
                        ->where('id', $quest->id)
                        ->update([
                            'quest_tier' => $tier,
                            'unlock_level' => QuestTier::unlockLevel($tier),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('quests', function (Blueprint $table): void {
            $table->dropColumn('quest_tier');
        });
    }
};
