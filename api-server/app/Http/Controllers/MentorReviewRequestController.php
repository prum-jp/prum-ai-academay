<?php

namespace App\Http\Controllers;

use App\Http\Resources\MentorReviewRequestResource;
use App\Services\MentorReviewRequestService;
use Illuminate\Http\JsonResponse;

class MentorReviewRequestController extends Controller
{
    public function __construct(
        private readonly MentorReviewRequestService $mentorReviewRequestService,
    ) {}

    public function index(): JsonResponse
    {
        $items = $this->mentorReviewRequestService->listReviewRequests();

        return response()->json([
            'data' => $items
                ->map(fn ($progress) => (new MentorReviewRequestResource($progress))->resolve())
                ->values(),
            'meta' => [
                'total' => $items->count(),
            ],
        ]);
    }
}
