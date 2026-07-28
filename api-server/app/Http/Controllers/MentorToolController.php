<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMentorToolRequest;
use App\Http\Resources\ToolResource;
use App\Models\Tool;
use App\Services\MentorToolRegistrar;
use Illuminate\Http\JsonResponse;

class MentorToolController extends Controller
{
    public function __construct(
        private readonly MentorToolRegistrar $mentorToolRegistrar,
    ) {}

    public function index(): JsonResponse
    {
        $tools = Tool::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json([
            'data' => ToolResource::collection($tools)->resolve(),
        ]);
    }

    public function store(StoreMentorToolRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tool = $this->mentorToolRegistrar->register([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
        ]);

        return response()->json([
            'data' => (new ToolResource($tool))->resolve(),
        ], 201);
    }
}
