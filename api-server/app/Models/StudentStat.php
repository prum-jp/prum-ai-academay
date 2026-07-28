<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentStat extends Model
{
    protected $fillable = [
        'user_id',
        'stat_presentation',
        'stat_communication',
        'stat_problem_finding',
        'stat_ai_affinity',
        'stat_action',
        'stat_support',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
