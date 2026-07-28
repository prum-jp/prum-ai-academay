<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    public const UNLOCK_QUEST_COMPLETE = 'quest_complete';

    protected $fillable = [
        'code',
        'title',
        'description',
        'icon',
        'unlock_type',
        'unlock_quest_id',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unlock_quest_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function unlockQuest(): BelongsTo
    {
        return $this->belongsTo(Quest::class, 'unlock_quest_id');
    }

    public function studentBadges(): HasMany
    {
        return $this->hasMany(StudentBadge::class);
    }
}
