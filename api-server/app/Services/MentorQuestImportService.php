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
    public function preview(array $items): array
    {
        $items = $this->validator->injectBatchUnitTitles($items);
        $toolCodes = $this->toolResolver->loadToolCodeMap();

        return array_map(function (array $item) use ($toolCodes): array {
            $preview = $this->enricher->enrichItem($item, toolCodes: $toolCodes);
            $preview['errors'] = $this->validator->validateItem($preview);
            unset($preview['_batchUnitTitles']);

            return $preview;
        }, $items);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    public function apply(array $items): array
    {
        $items = $this->validator->injectBatchUnitTitles($items);
        $toolCodes = $this->toolResolver->loadToolCodeMap();
        $batchUnitTitles = $this->validator->collectBatchUnitTitles($items);

        $enrichedItems = array_map(function (array $item) use ($toolCodes): array {
            $enriched = $this->enricher->enrichItem($item, toolCodes: $toolCodes);
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

        return DB::transaction(function () use ($enrichedItems, $toolCodes): array {
            $toolCodes = $this->toolResolver->ensureToolsRegistered($enrichedItems, $toolCodes);
            $results = [];

            foreach ($enrichedItems as $item) {
                $results[] = $this->applier->applyItem($item, $toolCodes);
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
