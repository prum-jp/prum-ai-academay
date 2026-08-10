<?php

namespace App\Services;

use App\Models\Quest;

class QuestToolSync
{
    /**
     * @param  list<int|null|mixed>  $toolIds
     */
    public function syncForQuest(Quest $quest, array $toolIds): void
    {
        $normalized = [];

        foreach ($toolIds as $toolId) {
            if ($toolId === null || $toolId === '') {
                continue;
            }

            $id = (int) $toolId;
            if ($id <= 0 || in_array($id, $normalized, true)) {
                continue;
            }

            $normalized[] = $id;
        }

        $sync = [];
        foreach ($normalized as $index => $toolId) {
            $sync[$toolId] = ['sort_order' => $index + 1];
        }

        $quest->tools()->sync($sync);
        $quest->update(['tool_id' => $normalized[0] ?? null]);
    }
}
