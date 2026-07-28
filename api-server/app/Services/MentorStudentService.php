<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MentorStudentService
{
    public const PER_PAGE = 5;

    public function paginateForMentor(?string $search, int $page): LengthAwarePaginator
    {
        return $this->studentQuery($search)
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page))
            ->withQueryString();
    }

    public function findStudent(int $studentId): User
    {
        return $this->studentQuery(null)
            ->whereKey($studentId)
            ->firstOrFail();
    }

    private function studentQuery(?string $search): Builder
    {
        $query = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->with(['studentProfile', 'studentStat'])
            ->withCount('studentBadges')
            ->orderBy('id');

        $search = trim((string) $search);
        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }
}
