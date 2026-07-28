<?php

namespace App\Http\Resources;

use App\Models\Badge;
use App\Models\StudentBadge;
use App\Models\User;
use App\Services\LevelCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin User
 */
class AdventurerProfileResource extends JsonResource
{
    public static $wrap = null;

    public function __construct(
        User $resource,
        private readonly LevelCalculator $levelCalculator,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile = $this->studentProfile;
        $stat = $this->studentStat;

        $stats = [
            'presentation' => (int) ($stat?->stat_presentation ?? 0),
            'communication' => (int) ($stat?->stat_communication ?? 0),
            'problemFinding' => (int) ($stat?->stat_problem_finding ?? 0),
            'aiAffinity' => (int) ($stat?->stat_ai_affinity ?? 0),
            'action' => (int) ($stat?->stat_action ?? 0),
            'support' => (int) ($stat?->stat_support ?? 0),
        ];

        $level = $this->levelCalculator->calculate(array_sum($stats));

        $avatarPath = $profile?->avatar_path;

        return [
            'name' => $this->name,
            'background' => $profile?->background ?? '',
            'hobby' => $profile?->hobby ?? '',
            'avatarUrl' => $avatarPath ? Storage::disk('public')->url($avatarPath) : null,
            'weaponSkill' => $profile?->weapon_skill ?? '',
            'spellGoal' => $profile?->spell_goal ?? '',
            'stats' => $stats,
            'level' => $level['level'],
            'levelTitle' => $level['level_title'],
            'total' => $level['total'],
            'progressPercent' => $level['progress_percent'],
            'earnedBadgeCount' => StudentBadge::query()->where('user_id', $this->id)->count(),
            'totalBadgeCount' => Badge::query()->count(),
        ];
    }
}
