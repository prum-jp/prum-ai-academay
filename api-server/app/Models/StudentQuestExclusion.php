<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuestExclusion extends Model
{
    protected $fillable = [
        'user_id',
        'quest_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }
}
