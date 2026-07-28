<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestUnit extends Model
{
    protected $fillable = [
        'title',
        'description',
        'reward_text',
        'sort_order',
        'is_published',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(QuestUnitReward::class);
    }

    public function quests(): HasMany
    {
        return $this->hasMany(Quest::class)->orderBy('sort_order')->orderBy('id');
    }
}
