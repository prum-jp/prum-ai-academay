<?php

namespace App\Models;

use App\Support\PublicStorage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuestSubmissionFile extends Model
{
    protected $fillable = [
        'student_quest_progress_id',
        'storage_path',
        'sort_order',
    ];

    public function progress(): BelongsTo
    {
        return $this->belongsTo(StudentQuestProgress::class, 'student_quest_progress_id');
    }

    /**
     * @return array{id: int, url: string|null}
     */
    public function toApiPayload(): array
    {
        return [
            'id' => (int) $this->id,
            'url' => PublicStorage::urlForStored($this->storage_path),
        ];
    }
}
