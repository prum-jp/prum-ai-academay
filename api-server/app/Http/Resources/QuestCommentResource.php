<?php

namespace App\Http\Resources;

use App\Models\StudentQuestComment;
use App\Support\QuestActivityType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin StudentQuestComment
 */
class QuestCommentResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $author */
        $author = $this->relationLoaded('author') ? $this->author : null;
        $avatarPath = $author?->studentProfile?->avatar_path;

        return [
            'id' => $this->id,
            'type' => $this->type ?? QuestActivityType::COMMENT,
            'body' => $this->body,
            'metadata' => $this->metadata,
            'authorId' => $this->author_id,
            'authorName' => $author?->name ?? '',
            'authorRole' => $author?->role === User::ROLE_MENTOR ? 'mentor' : 'student',
            'authorAvatarUrl' => $avatarPath ? Storage::disk('public')->url($avatarPath) : null,
            'createdAt' => $this->created_at?->toIso8601String(),
            'isOwn' => $request->user()?->id === $this->author_id,
        ];
    }
}
