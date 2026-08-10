<?php

namespace App\Services;

use App\Models\Quest;
use App\Support\QuestDifficulty;
use App\Support\QuestTier;
use Illuminate\Support\Facades\DB;

class MentorQuestRegistrar
{
    public function __construct(
        private readonly QuestSkillGrantSync $questSkillGrantSync,
        private readonly QuestToolSync $questToolSync,
    ) {}

    /**
     * @param  array{
     *     type: string,
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     isRequired?: bool,
     *     unlockLevel?: int|null,
     *     rewardText?: string|null,
     *     badgeLabel?: string|null,
     *     difficulty?: int|null,
     *     skillGrants?: list<string>,
     *     toolId?: int|null,
     *     toolIds?: list<int|null>
     * }  $payload
     */
    public function register(array $payload): Quest
    {
        return DB::transaction(function () use ($payload): Quest {
            $type = $payload['type'];
            $toolIds = $this->resolveToolIds($payload);

            $difficulty = QuestDifficulty::normalize($payload['difficulty'] ?? null);

            $quest = Quest::query()->create([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'clear_condition' => $payload['clearCondition'] ?? '',
                'type' => $type,
                'quest_unit_id' => null,
                'tool_id' => $toolIds[0] ?? null,
                'is_required' => $payload['isRequired'] ?? true,
                'unlock_level' => $payload['unlockLevel'] ?? null,
                'reward_text' => $payload['rewardText'] ?? '',
                'badge_label' => $payload['badgeLabel'] ?? null,
                'difficulty' => $difficulty,
                'experience_points' => QuestDifficulty::experiencePoints($difficulty),
                'brand_label' => null,
                'sort_order' => ((int) Quest::query()
                    ->where('type', $type)
                    ->whereNull('quest_unit_id')
                    ->max('sort_order')) + 1,
                'is_published' => false,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths(2)->toDateString(),
            ]);

            $this->questSkillGrantSync->syncForQuest($quest, $payload['skillGrants'] ?? []);
            $this->questToolSync->syncForQuest($quest, $toolIds);

            return $quest->load(['rewards', 'tools']);
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     isRequired?: bool,
     *     unlockLevel?: int|null,
     *     rewardText?: string|null,
     *     badgeLabel?: string|null,
     *     difficulty?: int|null,
     *     skillGrants?: list<string>,
     *     toolId?: int|null,
     *     toolIds?: list<int|null>
     * }  $payload
     */
    public function update(Quest $quest, array $payload): Quest
    {
        return DB::transaction(function () use ($quest, $payload): Quest {
            $toolIds = $this->resolveToolIds($payload);
            $difficulty = QuestDifficulty::normalize($payload['difficulty'] ?? null);

            $quest->update([
                'title' => $payload['title'],
                'description' => $payload['description'] ?? '',
                'clear_condition' => $payload['clearCondition'] ?? '',
                'is_required' => $payload['isRequired'] ?? true,
                'unlock_level' => $payload['unlockLevel'] ?? null,
                'reward_text' => $payload['rewardText'] ?? '',
                'badge_label' => $payload['badgeLabel'] ?? null,
                'difficulty' => $difficulty,
                'experience_points' => QuestDifficulty::experiencePoints($difficulty),
                'tool_id' => $toolIds[0] ?? null,
            ]);

            $this->questSkillGrantSync->syncForQuest($quest, $payload['skillGrants'] ?? []);
            $this->questToolSync->syncForQuest($quest, $toolIds);

            return $quest->fresh(['rewards', 'tools']);
        });
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     toolId?: int|null,
     *     toolIds?: list<int|null>,
     *     estimatedDuration?: string|null,
     *     difficulty?: int|null,
     *     skillGrants?: list<string>,
     *     questTier?: string|null
     * }  $payload
     */
    public function updatePersonal(Quest $quest, array $payload): Quest
    {
        $toolIds = $this->resolveToolIds($payload);
        $difficulty = QuestDifficulty::normalize($payload['difficulty'] ?? null);

        $attributes = [
            'title' => $payload['title'],
            'description' => $payload['description'] ?? '',
            'clear_condition' => $payload['clearCondition'] ?? '',
            'tool_id' => $toolIds[0] ?? null,
            'estimated_duration' => ($payload['estimatedDuration'] ?? null) !== null && $payload['estimatedDuration'] !== ''
                ? (string) $payload['estimatedDuration']
                : null,
            'difficulty' => $difficulty,
            'experience_points' => QuestDifficulty::experiencePoints($difficulty),
        ];
        QuestTier::applyToAttributes($attributes, $payload['questTier'] ?? QuestTier::LOW);

        $quest->update($attributes);

        $this->questSkillGrantSync->syncForQuest($quest, $payload['skillGrants'] ?? []);
        $this->questToolSync->syncForQuest($quest, $toolIds);

        return $quest->fresh(['tool', 'tools', 'questUnit', 'rewards']);
    }

    /**
     * @param  array{toolId?: int|null, toolIds?: list<int|null>}  $payload
     * @return list<int>
     */
    private function resolveToolIds(array $payload): array
    {
        $toolIds = $payload['toolIds'] ?? [];
        if ($toolIds !== []) {
            return array_values(array_filter(
                array_map(static fn ($id) => $id !== null ? (int) $id : null, $toolIds),
                static fn ($id) => $id !== null && $id > 0,
            ));
        }

        if (($payload['toolId'] ?? null) !== null) {
            return [(int) $payload['toolId']];
        }

        return [];
    }

    public function setPublished(Quest $quest, bool $isPublished): Quest
    {
        $quest->update(['is_published' => $isPublished]);

        return $quest->fresh(['rewards']);
    }

    public function delete(Quest $quest): void
    {
        $quest->delete();
    }
}
