<?php

namespace App\Services;

use App\Models\Quest;
use App\Support\ChildQuestAttributeBuilder;
use App\Support\QuestToolIdResolver;

class PersonalQuestWriter
{
    public function __construct(
        private readonly QuestSkillGrantSync $questSkillGrantSync,
        private readonly QuestToolSync $questToolSync,
    ) {}

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
        $toolIds = QuestToolIdResolver::resolve($payload);
        $attributes = ChildQuestAttributeBuilder::fromPayload($payload, includeEstimatedDuration: true);
        $attributes['tool_id'] = $toolIds[0] ?? null;

        $quest->update($attributes);
        $this->syncRelations($quest, $payload, $toolIds);

        return $quest->fresh(['tool', 'tools', 'questUnit', 'rewards']);
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     toolId?: int|null,
     *     toolIds?: list<int|null>,
     *     sortOrder?: int|null,
     *     difficulty?: int|null,
     *     skillGrants?: list<string>,
     *     questTier?: string|null
     * }  $questData
     * @return array<string, mixed>
     */
    public function buildUnitChildAttributes(array $questData, int $fallbackSortOrder): array
    {
        $toolIds = QuestToolIdResolver::resolve($questData);
        $attributes = ChildQuestAttributeBuilder::fromPayload($questData);
        $attributes['tool_id'] = $toolIds[0] ?? null;
        $attributes['sort_order'] = $questData['sortOrder'] ?? $fallbackSortOrder;
        $attributes['is_published'] = true;

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function newUnitChildDefaults(): array
    {
        return [
            'type' => Quest::TYPE_PERSONAL,
            'is_required' => true,
            'reward_text' => null,
            'badge_label' => null,
            'brand_label' => null,
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(2)->toDateString(),
        ];
    }

    /**
     * @param  array{
     *     title: string,
     *     description?: string|null,
     *     clearCondition?: string|null,
     *     difficulty?: int|null,
     *     skillGrants?: list<string>,
     *     questTier?: string|null,
     *     isRequired?: bool
     * }  $item
     * @return array<string, mixed>
     */
    public function buildImportAttributes(array $item, ?int $toolId, int $sortOrder, int $unitId): array
    {
        $attributes = ChildQuestAttributeBuilder::fromPayload($item);
        $attributes['tool_id'] = $toolId;
        $attributes['sort_order'] = $sortOrder;
        $attributes['is_published'] = true;
        $attributes['type'] = Quest::TYPE_PERSONAL;
        $attributes['quest_unit_id'] = $unitId;
        $attributes['is_required'] = (bool) ($item['isRequired'] ?? true);
        $attributes['reward_text'] = null;
        $attributes['badge_label'] = null;
        $attributes['brand_label'] = null;

        return $attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function newImportDefaults(): array
    {
        return [
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addMonths(2)->toDateString(),
        ];
    }

    /**
     * @param  array{skillGrants?: list<string>}  $payload
     * @param  list<int>  $toolIds
     */
    public function syncRelations(Quest $quest, array $payload, array $toolIds): void
    {
        $this->questSkillGrantSync->syncForQuest($quest, $payload['skillGrants'] ?? []);
        $this->questToolSync->syncForQuest($quest, $toolIds);
    }
}
