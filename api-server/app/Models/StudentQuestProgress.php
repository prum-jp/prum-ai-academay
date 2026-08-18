<?php

namespace App\Models;

use App\Support\QuestProgressStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentQuestProgress extends Model
{
    protected $table = 'student_quest_progress';

    protected $fillable = [
        'user_id',
        'quest_id',
        'status',
        'submission_url',
        'submission_type',
        'submission_text',
        'is_completed',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public static function firstOrInitializeFor(User $student, Quest $quest): self
    {
        $progress = self::query()->firstOrNew([
            'user_id' => $student->id,
            'quest_id' => $quest->id,
        ]);

        if (! $progress->exists) {
            QuestProgressStatus::applyToProgress($progress, QuestProgressStatus::NOT_STARTED);
        }

        return $progress;
    }

    public function ensureExists(): self
    {
        if (! $this->exists) {
            $this->save();
        }

        return $this;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quest(): BelongsTo
    {
        return $this->belongsTo(Quest::class);
    }

    public function submissionFiles(): HasMany
    {
        return $this->hasMany(StudentQuestSubmissionFile::class, 'student_quest_progress_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeForStudent(Builder $query, User $student): Builder
    {
        return $query->where('user_id', $student->id);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithSubmissionPayload(Builder $query): Builder
    {
        return $query->with('submissionFiles');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeHasAnySubmission(Builder $query): Builder
    {
        return $query->where(function (Builder $inner): void {
            $inner->where(function (Builder $submissionQuery): void {
                $submissionQuery->whereNotNull('submission_url')
                    ->where('submission_url', '!=', '');
            })->orWhere(function (Builder $submissionQuery): void {
                $submissionQuery->whereNotNull('submission_text')
                    ->where('submission_text', '!=', '');
            })->orWhereHas('submissionFiles');
        });
    }
}
