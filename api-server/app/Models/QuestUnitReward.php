<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestUnitReward extends Model
{
    protected $fillable = [
        'quest_unit_id',
        'stat',
        'points',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'points' => 'integer',
        ];
    }

    public function questUnit(): BelongsTo
    {
        return $this->belongsTo(QuestUnit::class);
    }
}
