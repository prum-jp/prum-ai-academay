<?php

namespace App\Services;

use App\Models\Quest;
use App\Support\SkillKeys;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestSkillGrantSync
{
    /**
     * @param  list<string>  $skillGrants
     */
    public function syncForQuest(Quest $quest, array $skillGrants): void
    {
        $this->syncRelation($quest->rewards(), SkillKeys::normalizeList($skillGrants));
    }

    /**
     * @param  HasMany<\App\Models\QuestReward>  $relation
     * @param  list<string>  $skillGrants
     */
    private function syncRelation(HasMany $relation, array $skillGrants): void
    {
        $relation->delete();

        foreach ($skillGrants as $skill) {
            $relation->create([
                'stat' => $skill,
                'points' => 1,
            ]);
        }
    }
}
