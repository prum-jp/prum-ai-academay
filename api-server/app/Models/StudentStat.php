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
}
