<?php

namespace App\Services;

use App\Services\QuestImport\QuestImportItemApplier;
use App\Services\QuestImport\QuestImportItemEnricher;
use App\Services\QuestImport\QuestImportItemValidator;
use App\Services\QuestImport\QuestImportToolResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MentorQuestImportService
{
    public function __construct(
        private readonly QuestImportItemValidator $validator,
        private readonly QuestImportItemEnricher $enricher,
        private readonly QuestImportItemApplier $applier,
        private readonly QuestImportToolResolver $toolResolver,
    ) {}

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function preview(array $items, ?string $defaultQuestTier = null): array
    {
        $items = $this->validator->injectBatchUnitTitles($items);
        $toolCodes = $this->toolResolver->loadToolNameMap();

        return array_map(function (array $item) use ($toolCodes, $defaultQuestTier): array {
            $preview = $this->enricher->enrichItem(
                $item,
                toolCodes: $toolCodes,
                logUnchangedDiff: (bool) config('app.debug'),
                defaultQuestTier: $defaultQuestTier,
            );
            $preview['errors'] = $this->validator->validateItem($preview);
            unset($preview['_batchUnitTitles']);

            return $preview;
        }, $items);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function apply(array $items, ?string $defaultQuestTier = null): array
    {
        $items = $this->validator->injectBatchUnitTitles($items);
        $toolCodes = $this->toolResolver->loadToolNameMap();
        $batchUnitTitles = $this->validator->collectBatchUnitTitles($items);

        $enrichedItems = array_map(function (array $item) use ($toolCodes, $defaultQuestTier): array {
            $enriched = $this->enricher->enrichItem(
                $item,
                toolCodes: $toolCodes,
                defaultQuestTier: $defaultQuestTier,
            );
            unset($enriched['_batchUnitTitles']);

            return $enriched;
        }, $items);

        foreach ($enrichedItems as $item) {
            $errors = $this->validator->validateItem([
                ...$item,
                '_batchUnitTitles' => $batchUnitTitles,
            ]);

            if ($errors !== []) {
                throw ValidationException::withMessages([
                    'items' => ['インポート内容にエラーがあります。プレビューを確認してください。'],
                ]);
            }
        }

        return DB::transaction(function () use ($enrichedItems, $toolCodes, $defaultQuestTier): array {
            $toolCodes = $this->toolResolver->ensureToolsRegistered($enrichedItems, $toolCodes);
            $results = [];

            foreach ($enrichedItems as $item) {
                $results[] = $this->applier->applyItem($item, $toolCodes, $defaultQuestTier);
            }

            return $results;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{total: int, createCount: int, updateCount: int, unchangedCount: int, errorCount: int}
     */
    public function summarize(array $items): array
    {
        $createCount = 0;
        $updateCount = 0;
        $unchangedCount = 0;
        $errorCount = 0;

        foreach ($items as $item) {
            $action = (string) ($item['action'] ?? 'create');

            if ($action === 'update') {
                $updateCount++;
            } elseif ($action === 'unchanged') {
                $unchangedCount++;
            } else {
                $createCount++;
            }

            if (! empty($item['errors'])) {
                $errorCount++;
            }
        }

        return [
            'total' => count($items),
            'createCount' => $createCount,
            'updateCount' => $updateCount,
            'unchangedCount' => $unchangedCount,
            'errorCount' => $errorCount,
        ];
    }
}
