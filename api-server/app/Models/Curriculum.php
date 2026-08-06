<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Curriculum extends Model
{
    protected $table = 'curricula';

    protected $fillable = [
        'name',
        'description',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function questUnits(): BelongsToMany
    {
        return $this->belongsToMany(QuestUnit::class, 'curriculum_quest_units')
            ->withPivot('sort_order')
            ->orderByPivot('sort_order')
            ->orderBy('quest_units.id');
    }

    public function studentAssignments(): HasMany
    {
        return $this->hasMany(StudentCurriculumAssignment::class);
    }
}
