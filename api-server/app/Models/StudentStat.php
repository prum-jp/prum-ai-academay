<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStat extends Model
{
    protected $fillable = [
        'user_id',
        'stat_business_skill',
        'stat_human_skill',
        'stat_conceptual_skill',
        'total_xp',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stat_business_skill' => 'integer',
            'stat_human_skill' => 'integer',
            'stat_conceptual_skill' => 'integer',
            'total_xp' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function ensureForUser(User $user): self
    {
        if ($user->relationLoaded('studentStat') && $user->studentStat !== null) {
            return $user->studentStat;
        }

        $stat = self::query()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'stat_business_skill' => 0,
                'stat_human_skill' => 0,
                'stat_conceptual_skill' => 0,
                'total_xp' => 0,
            ],
        );

        $user->setRelation('studentStat', $stat);

        return $stat;
    }

    public static function findForUser(User $user): ?self
    {
        if ($user->relationLoaded('studentStat')) {
            return $user->studentStat;
        }

        $stat = self::query()->where('user_id', $user->id)->first();

        if ($stat !== null) {
            $user->setRelation('studentStat', $stat);
        }

        return $stat;
    }
}
