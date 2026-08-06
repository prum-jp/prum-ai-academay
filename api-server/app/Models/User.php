<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_STUDENT = 0;

    public const ROLE_MENTOR = 1;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'integer',
        ];
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function studentStat(): HasOne
    {
        return $this->hasOne(StudentStat::class);
    }

    public function studentBadges(): HasMany
    {
        return $this->hasMany(StudentBadge::class);
    }

    public function curriculumAssignments(): HasMany
    {
        return $this->hasMany(StudentCurriculumAssignment::class);
    }

    public function questUnitAssignments(): HasMany
    {
        return $this->hasMany(StudentQuestUnitAssignment::class);
    }

    public function questAssignments(): HasMany
    {
        return $this->hasMany(StudentQuestAssignment::class);
    }

    public function questExclusions(): HasMany
    {
        return $this->hasMany(StudentQuestExclusion::class);
    }

    public function isMentor(): bool
    {
        return $this->role === self::ROLE_MENTOR;
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }
}
