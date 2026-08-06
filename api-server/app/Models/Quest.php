<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quest extends Model
{
    public const TYPE_PERSONAL = 'personal';

    public const TYPE_TEAM = 'team';

    public const TYPE_SPECIAL = 'special';

    /**
     * @var list<string>
     */
    public const TYPES = [
        self::TYPE_PERSONAL,
        self::TYPE_TEAM,
        self::TYPE_SPECIAL,
    ];

    protected $fillable = [
        'title',
        'description',
        'clear_condition',
        'estimated_duration',
        'difficulty',
        'experience_points',
        'type',
        'quest_unit_id',
        'tool_id',
        'is_required',
        'unlock_level',
        'quest_tier',
        'reward_text',
        'badge_label',
        'brand_label',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'unlock_level' => 'integer',
            'difficulty' => 'integer',
            'experience_points' => 'integer',
            'starts_at' => 'date',
            'ends_at' => 'date',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function questUnit(): BelongsTo
    {
        return $this->belongsTo(QuestUnit::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(QuestApplication::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(QuestReward::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(StudentQuestProgress::class);
    }

    public function progressFor(User $user): HasOne
    {
        return $this->hasOne(StudentQuestProgress::class)->where('user_id', $user->id);
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentQuestAssignment::class);
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(StudentQuestExclusion::class);
    }
}
