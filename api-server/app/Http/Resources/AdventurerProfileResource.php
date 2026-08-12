<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\StudentLevelResolver;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class AdventurerProfileResource extends JsonResource
{
    public static $wrap = null;

    public function __construct(
        User $resource,
        private readonly StudentLevelResolver $studentLevelResolver,
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
            'businessSkill' => (int) ($stat?->stat_business_skill ?? 0),
            'humanSkill' => (int) ($stat?->stat_human_skill ?? 0),
            'conceptualSkill' => (int) ($stat?->stat_conceptual_skill ?? 0),
        ];

        $level = $this->studentLevelResolver->resolvePayload($this->resource);

        return [
            'name' => $this->name,
            'background' => $profile?->background ?? '',
            'hobby' => $profile?->hobby ?? '',
            'avatarUrl' => $this->studentLevelResolver->resolveAvatarUrl($this->resource),
            'weaponSkill' => $profile?->weapon_skill ?? '',
            'spellGoal' => $profile?->spell_goal ?? '',
            'stats' => $stats,
            'level' => $level['level'],
            'levelTitle' => $level['level_title'],
            'total' => $level['total'],
            'progressPercent' => $level['progress_percent'],
            'xpCurrentLevelMin' => $level['xp_current_level_min'],
            'xpNextLevelMin' => $level['xp_next_level_min'],
            // TODO: 後に機能追加 — 実績バッジ獲得数
            'earnedBadgeCount' => 0,
            'totalBadgeCount' => 0,
            // 'earnedBadgeCount' => StudentBadge::query()->where('user_id', $this->id)->count(),
            // 'totalBadgeCount' => Badge::query()->count(),
        ];
    }
}
