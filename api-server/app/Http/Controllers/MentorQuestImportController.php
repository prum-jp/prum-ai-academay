<?php

namespace App\Http\Controllers;

use App\Http\Requests\MentorQuestImportRequest;
use App\Services\MentorQuestImportService;
use Illuminate\Http\JsonResponse;

class MentorQuestImportController extends Controller
{
    public function __construct(
        private readonly MentorQuestImportService $mentorQuestImportService,
    ) {}

    public function preview(MentorQuestImportRequest $request): JsonResponse
    {
        $items = $this->mentorQuestImportService->preview($request->validated('items'));

        return response()->json([
            'data' => $items,
            'meta' => $this->mentorQuestImportService->summarize($items),
        ]);
    }

    public function apply(MentorQuestImportRequest $request): JsonResponse
    {
        $items = $request->validated('items');
        $results = $this->mentorQuestImportService->apply($items);

        return response()->json([
            'data' => $results,
            'meta' => [
                'appliedCount' => count($results),
            ],
        ]);
    }
}
