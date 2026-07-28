<?php

namespace App\Http\Controllers;

use App\Http\Resources\BadgeResource;
use App\Models\Badge;
use App\Support\AdventurerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $student = AdventurerContext::targetStudent($request);

        $badges = Badge::query()
            ->with(['studentBadges' => function ($query) use ($student): void {
                $query->where('user_id', $student->id);
            }])
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $earnedCount = $badges->filter(
            fn (Badge $badge) => $badge->studentBadges->isNotEmpty(),
        )->count();

        return response()->json([
            'data' => $badges->map(
                fn (Badge $badge) => (new BadgeResource($badge))->resolve(),
            )->values(),
            'meta' => [
                'earnedCount' => $earnedCount,
                'totalCount' => $badges->count(),
            ],
        ]);
    }
}
