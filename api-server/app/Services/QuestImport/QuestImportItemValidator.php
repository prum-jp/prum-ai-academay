<?php

namespace App\Services\QuestImport;

use App\Models\QuestUnit;
use App\Support\QuestTier;
use App\Support\SkillKeys;

class QuestImportItemValidator
{
    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function injectBatchUnitTitles(array $items): array
    {
        $batchUnitTitles = $this->collectBatchUnitTitles($items);

        return array_map(function (array $item) use ($batchUnitTitles): array {
            $item['_batchUnitTitles'] = $batchUnitTitles;

            return $item;
        }, $items);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<string>
     */
    public function collectBatchUnitTitles(array $items): array
    {
        $titles = [];

        foreach ($items as $item) {
            if (($item['kind'] ?? '') !== 'personal_unit') {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            if ($title !== '') {
                $titles[] = $title;
            }
        }

        return array_values(array_unique($titles));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return list<string>
     */
    public function validateItem(array $item): array
    {
        $errors = [];
        $kind = (string) ($item['kind'] ?? '');

        if (trim((string) ($item['title'] ?? '')) === '') {
            $errors[] = 'タイトルは必須です。';
        }

        if ($kind === 'child_quest' && trim((string) ($item['unitTitle'] ?? '')) === '') {
            $errors[] = '子クエストには Unit名 が必要です。';
        }

        if ($kind === 'child_quest') {
            $unitTitle = (string) ($item['unitTitle'] ?? '');
            $unitExists = QuestUnit::query()->where('title', $unitTitle)->exists();
            $unitInBatch = in_array($unitTitle, $item['_batchUnitTitles'] ?? [], true);

            if (! $unitExists && ! $unitInBatch) {
                $errors[] = "ユニット「{$unitTitle}」が見つかりません。Unit名を確認するか、対応するユニット行を追加してください。";
            }
        }

        foreach ($item['skillGrants'] ?? [] as $skill) {
            if (! in_array((string) $skill, SkillKeys::ALL, true)) {
                $errors[] = "スキル「{$skill}」が不正です。";
            }
        }

        if (
            in_array($kind, ['child_quest', 'team_quest', 'special_quest'], true)
            && array_key_exists('questTier', $item)
            && $item['questTier'] !== null
            && $item['questTier'] !== ''
            && ! QuestTier::isRecognized($item['questTier'])
        ) {
            $errors[] = 'クエストTier が不正です。';
        }

        return $errors;
    }
}
