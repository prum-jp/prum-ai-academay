<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\StudentExperienceService;
use App\Support\PublicStorage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class StudentListItemResource extends JsonResource
{
    public static $wrap = null;

    public function __construct(
        User $resource,
        private readonly LevelCalculator $levelCalculator,
        private readonly StudentExperienceService $studentExperienceService,
        private readonly ?int $selectedStudentId = null,
        private readonly bool $includeEmail = false,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalXp = $this->studentExperienceService->totalXp($this->resource);
        $level = $this->levelCalculator->calculate($totalXp);
        $avatarPath = $this->studentProfile?->avatar_path;

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'avatarUrl' => $avatarPath ? PublicStorage::url($avatarPath) : null,
            'levelTitle' => $level['level_title'],
            // TODO: 後に機能追加 — 実績バッジ獲得数
            'earnedBadgeCount' => 0,
            // 'earnedBadgeCount' => (int) ($this->student_badges_count ?? 0),
        ];

        if ($this->includeEmail) {
            $data['email'] = $this->email;
        }

        if ($this->selectedStudentId !== null) {
            $data['isSelected'] = $this->id === $this->selectedStudentId;
        }

        return $data;
    }
}
