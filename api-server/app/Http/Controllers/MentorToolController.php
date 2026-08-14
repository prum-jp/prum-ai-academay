<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMentorToolRequest;
use App\Http\Requests\UpdateMentorToolRequest;
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
            'name' => $validated['name'],
            'icon' => $validated['icon'] ?? null,
        ]);

        return response()->json([
            'data' => (new ToolResource($tool))->resolve(),
        ], 201);
    }

    public function update(UpdateMentorToolRequest $request, Tool $tool): JsonResponse
    {
        $validated = $request->validated();
        $payload = [
            'name' => $validated['name'],
        ];

        if (array_key_exists('icon', $validated)) {
            $payload['icon'] = $validated['icon'];
        }

        $tool = $this->mentorToolRegistrar->update($tool, $payload);

        return response()->json([
            'data' => (new ToolResource($tool))->resolve(),
        ]);
    }
}
