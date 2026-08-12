<?php

namespace App\Services\QuestImport;

use App\Models\Tool;
use App\Services\MentorToolRegistrar;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuestImportToolResolver
{
    public function __construct(
        private readonly MentorToolRegistrar $mentorToolRegistrar,
    ) {}

    /**
     * @return Collection<string, int>
     */
    public function loadToolCodeMap(): Collection
    {
        return Tool::query()->pluck('id', 'code');
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
     * @param  Collection<string, int>  $toolCodes
     * @return list<int>
     */
    public function resolveToolIds(string $toolRef, Collection $toolCodes): array
    {
        $ids = [];

        foreach ($this->splitToolNames($toolRef) as $name) {
            $toolId = $this->resolveToolId($name, $toolCodes);
            if ($toolId !== null && ! in_array($toolId, $ids, true)) {
                $ids[] = $toolId;
            }
        }

        return $ids;
    }

    /**
     * @param  Collection<string, int>  $toolCodes
     */
    public function resolveFirstToolId(string $toolRef, Collection $toolCodes): ?int
    {
        $names = $this->splitToolNames($toolRef);
        if ($names === []) {
            return null;
        }

        return $this->resolveToolId($names[0], $toolCodes);
    }

    /**
     * @param  Collection<string, int>  $toolCodes
     */
    public function resolveToolId(string $toolRef, Collection $toolCodes): ?int
    {
        $normalized = trim($toolRef);
        if ($normalized === '') {
            return null;
        }

        if ($toolCodes->has($normalized)) {
            return (int) $toolCodes->get($normalized);
        }

        $lower = strtolower($normalized);
        if ($toolCodes->has($lower)) {
            return (int) $toolCodes->get($lower);
        }

        $byName = Tool::query()
            ->whereRaw('LOWER(name) = ?', [$lower])
            ->value('id');

        if ($byName !== null) {
            return (int) $byName;
        }

        $byCode = Tool::query()
            ->whereRaw('LOWER(code) = ?', [$lower])
            ->value('id');

        return $byCode !== null ? (int) $byCode : null;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @param  Collection<string, int>  $toolCodes
     * @return Collection<string, int>
     */
    public function ensureToolsRegistered(array $items, Collection $toolCodes): Collection
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
            if ($this->resolveToolId($toolRef, $toolCodes) !== null) {
                continue;
            }

            $tool = $this->mentorToolRegistrar->register([
                'code' => $this->generateUniqueToolCode($toolRef),
                'name' => $toolRef,
            ]);

            $toolCodes->put($tool->code, $tool->id);
            $toolCodes->put(strtolower($tool->code), $tool->id);
            $toolCodes->put($tool->name, $tool->id);
            $toolCodes->put(strtolower($tool->name), $tool->id);
        }

        return $toolCodes;
    }

    private function generateUniqueToolCode(string $name): string
    {
        $ascii = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?? '');
        $base = trim($ascii, '-');

        if ($base === '') {
            $base = 'tool-'.substr(md5($name), 0, 8);
        }

        $base = Str::limit($base, 30, '');
        $code = $base;
        $suffix = 2;

        while (Tool::query()->where('code', $code)->exists()) {
            $suffixPart = (string) $suffix;
            $code = Str::limit($base, max(1, 40 - strlen($suffixPart) - 1), '').'-'.$suffixPart;
            $suffix++;
        }

        return $code;
    }
}
