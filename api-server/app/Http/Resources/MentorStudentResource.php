<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\LevelCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin User
 */
class MentorStudentResource extends JsonResource
{
    public static $wrap = null;

    public function __construct(
        User $resource,
        private readonly LevelCalculator $levelCalculator,
        private readonly ?int $selectedStudentId = null,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $stat = $this->studentStat;
        $total = (int) ($stat?->stat_presentation ?? 0)
            + (int) ($stat?->stat_communication ?? 0)
            + (int) ($stat?->stat_problem_finding ?? 0)
            + (int) ($stat?->stat_ai_affinity ?? 0)
            + (int) ($stat?->stat_action ?? 0)
            + (int) ($stat?->stat_support ?? 0);

        $level = $this->levelCalculator->calculate($total);
        $avatarPath = $this->studentProfile?->avatar_path;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatarUrl' => $avatarPath ? Storage::disk('public')->url($avatarPath) : null,
            'levelTitle' => $level['level_title'],
            'earnedBadgeCount' => (int) ($this->student_badges_count ?? 0),
            'isSelected' => $this->selectedStudentId !== null && $this->id === $this->selectedStudentId,
        ];
    }
}
