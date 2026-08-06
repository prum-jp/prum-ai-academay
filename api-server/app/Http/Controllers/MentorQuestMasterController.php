<?php

namespace App\Http\Controllers;

use App\Services\MentorQuestMasterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MentorQuestMasterController extends Controller
{
    public function __construct(
        private readonly MentorQuestMasterService $mentorQuestMasterService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kind' => ['sometimes', 'nullable', 'string', Rule::in([
                'personal_unit',
                'child_quest',
                'team_quest',
                'special_quest',
            ])],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $kind = isset($validated['kind']) && $validated['kind'] !== ''
            ? (string) $validated['kind']
            : null;
        $search = isset($validated['search']) && trim((string) $validated['search']) !== ''
            ? trim((string) $validated['search'])
            : null;
        $page = max(1, (int) ($validated['page'] ?? 1));

        $result = $this->mentorQuestMasterService->paginateGrouped($kind, $search, $page);

        return response()->json([
            'data' => [
                'units' => $result['units'],
                'teamQuests' => $result['teamQuests'],
                'specialQuests' => $result['specialQuests'],
            ],
            'meta' => $result['meta'],
        ]);
    }

    public function export(): StreamedResponse
    {
        $csv = $this->mentorQuestMasterService->exportCsv();

        return response()->streamDownload(
            static function () use ($csv): void {
                echo $csv;
            },
            'quest-master.csv',
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ],
        );
    }
}
