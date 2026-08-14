<?php

namespace App\Services\QuestImport;

use App\Models\Tool;
use App\Services\MentorToolRegistrar;
use Illuminate\Support\Collection;

class QuestImportToolResolver
{
    public function __construct(
        private readonly MentorToolRegistrar $mentorToolRegistrar,
    ) {}

    /**
     * @return Collection<string, int>
     */
    public function loadToolNameMap(): Collection
    {
        $map = collect();

        foreach (Tool::query()->get(['id', 'name']) as $tool) {
            $this->putToolNameKeys($map, $tool->name, $tool->id);
        }

        return $map;
    }

    /**
     * @return list<string>
     */
    public function splitToolNames(string $raw): array
    {
        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $segments = preg_split('#[,、/／|・\n]+#u', $trimmed) ?: [];
        $names = [];

        foreach ($segments as $segment) {
            $part = trim($segment);
            if ($part === '') {
                continue;
            }

            $latinJpParts = preg_split('/\s+(?=[A-Z][a-zA-Z0-9]*[\x{3000}-\x{9fff}])/u', $part) ?: [$part];
            foreach ($latinJpParts as $name) {
                $name = trim($name);
                if ($name !== '') {
                    $names[] = $name;
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * @param  Collection<string, int>  $toolNameMap
     * @return list<int>
     */
    public function resolveToolIds(string $toolRef, Collection $toolNameMap): array
    {
        $ids = [];

        foreach ($this->splitToolNames($toolRef) as $name) {
            $toolId = $this->resolveToolId($name, $toolNameMap);
            if ($toolId !== null && ! in_array($toolId, $ids, true)) {
                $ids[] = $toolId;
            }
        }

        return $ids;
    }

    /**
     * @param  Collection<string, int>  $toolNameMap
     */
    public function resolveFirstToolId(string $toolRef, Collection $toolNameMap): ?int
    {
        $names = $this->splitToolNames($toolRef);
        if ($names === []) {
            return null;
        }

        return $this->resolveToolId($names[0], $toolNameMap);
    }

    /**
     * @param  Collection<string, int>  $toolNameMap
     */
    public function resolveToolId(string $toolRef, Collection $toolNameMap): ?int
    {
        $normalized = trim($toolRef);
        if ($normalized === '') {
            return null;
        }

        if ($toolNameMap->has($normalized)) {
            return (int) $toolNameMap->get($normalized);
        }

        $lower = Tool::normalizeName($normalized);
        if ($toolNameMap->has($lower)) {
            return (int) $toolNameMap->get($lower);
        }

        $byName = Tool::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$lower])
            ->value('id');

        return $byName !== null ? (int) $byName : null;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  Collection<string, int>  $toolNameMap
     * @return Collection<string, int>
     */
    public function ensureToolsRegistered(array $items, Collection $toolNameMap): Collection
    {
        $toolRefs = [];

        foreach ($items as $item) {
            if (($item['kind'] ?? '') !== 'child_quest') {
                continue;
            }

            $toolRef = trim((string) ($item['toolCode'] ?? ''));
            foreach ($this->splitToolNames($toolRef) as $name) {
                $toolRefs[] = $name;
            }
        }

        foreach (array_values(array_unique($toolRefs)) as $toolRef) {
            if ($this->resolveToolId($toolRef, $toolNameMap) !== null) {
                continue;
            }

            $tool = $this->mentorToolRegistrar->register([
                'name' => $toolRef,
            ]);

            $this->putToolNameKeys($toolNameMap, $tool->name, $tool->id);
        }

        return $toolNameMap;
    }

    /**
     * @param  Collection<string, int>  $toolNameMap
     */
    private function putToolNameKeys(Collection $toolNameMap, string $name, int $id): void
    {
        $toolNameMap->put($name, $id);
        $toolNameMap->put(Tool::normalizeName($name), $id);
    }
}
