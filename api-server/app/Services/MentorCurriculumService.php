<?php

namespace App\Services;

use App\Models\Curriculum;
use App\Models\QuestUnit;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MentorCurriculumService
{
    public const PER_PAGE = 10;

    public function __construct(
        private readonly MentorStudentAssignmentService $assignmentService,
    ) {}

    public function paginate(int $page): LengthAwarePaginator
    {
        return Curriculum::query()
            ->withCount('questUnits')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page));
    }

    public function find(int $id): Curriculum
    {
        return Curriculum::query()
            ->with(['questUnits' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')])
            ->findOrFail($id);
    }

    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     unitIds?: list<int>,
     *     assignmentTarget: string,
     *     studentIds?: list<int>
     * }  $payload
     */
    public function create(array $payload, User $mentor): Curriculum
    {
        return DB::transaction(function () use ($payload, $mentor): Curriculum {
            $curriculum = Curriculum::query()->create([
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
                'sort_order' => ((int) Curriculum::query()->max('sort_order')) + 1,
            ]);

            $this->syncUnits($curriculum, $payload['unitIds'] ?? []);
            $this->assignmentService->syncCurriculumAssignments(
                $curriculum,
                $payload['assignmentTarget'],
                $payload['studentIds'] ?? [],
                $mentor,
            );

            return $curriculum->loadCount('questUnits');
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     description?: string|null,
     *     unitIds?: list<int>,
     *     assignmentTarget: string,
     *     studentIds?: list<int>
     * }  $payload
     */
    public function update(Curriculum $curriculum, array $payload, User $mentor): Curriculum
    {
        return DB::transaction(function () use ($curriculum, $payload, $mentor): Curriculum {
            $curriculum->update([
                'name' => $payload['name'],
                'description' => $payload['description'] ?? null,
            ]);

            if (array_key_exists('unitIds', $payload)) {
                $this->syncUnits($curriculum, $payload['unitIds']);
            }

            $this->assignmentService->syncCurriculumAssignments(
                $curriculum,
                $payload['assignmentTarget'],
                $payload['studentIds'] ?? [],
                $mentor,
            );

            return $curriculum->loadCount('questUnits');
        });
    }

    public function delete(Curriculum $curriculum): void
    {
        $curriculum->delete();
    }

    /**
     * @param  list<int>  $unitIds
     */
    private function syncUnits(Curriculum $curriculum, array $unitIds): void
    {
        $syncData = [];

        foreach (array_values(array_unique($unitIds)) as $index => $unitId) {
            if (! QuestUnit::query()->whereKey($unitId)->exists()) {
                continue;
            }

            $syncData[$unitId] = ['sort_order' => $index + 1];
        }

        $curriculum->questUnits()->sync($syncData);
    }
}
