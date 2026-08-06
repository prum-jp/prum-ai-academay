<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MentorStudentService
{
    public const PER_PAGE = 5;

    public const PICKER_PER_PAGE = 20;

    public const DIRECTORY_PER_PAGE = 5;

    public function paginateForMentor(?string $search, int $page): LengthAwarePaginator
    {
        return $this->buildStudentListQuery($search)
            ->paginate(self::PER_PAGE, ['*'], 'page', max(1, $page))
            ->withQueryString();
    }

    public function paginateForPicker(?string $search, int $page): LengthAwarePaginator
    {
        return $this->buildStudentListQuery($search)
            ->paginate(self::PICKER_PER_PAGE, ['*'], 'page', max(1, $page))
            ->withQueryString();
    }

    public function paginateForDirectory(?string $search, int $page, ?int $excludeUserId = null): LengthAwarePaginator
    {
        return $this->buildStudentListQuery($search, [
            'excludeUserId' => $excludeUserId,
            'searchNameOnly' => true,
        ])
            ->paginate(self::DIRECTORY_PER_PAGE, ['*'], 'page', max(1, $page))
            ->withQueryString();
    }

    public function findStudent(int $studentId): User
    {
        return $this->buildStudentListQuery(null)
            ->whereKey($studentId)
            ->firstOrFail();
    }

    public function findNextDirectoryStudent(int $studentId, int $excludeUserId): ?User
    {
        $next = $this->buildStudentListQuery(null, [
            'excludeUserId' => $excludeUserId,
            'searchNameOnly' => true,
        ])
            ->where('id', '>', $studentId)
            ->orderBy('id')
            ->first();

        if ($next === null) {
            $next = $this->buildStudentListQuery(null, [
                'excludeUserId' => $excludeUserId,
                'searchNameOnly' => true,
            ])
                ->orderBy('id')
                ->first();
        }

        if ($next === null || $next->id === $studentId) {
            return null;
        }

        return $next;
    }

    /**
     * @param  array{excludeUserId?: int|null, searchNameOnly?: bool}  $options
     */
    private function buildStudentListQuery(?string $search, array $options = []): Builder
    {
        $query = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->with(['studentProfile', 'studentStat'])
            // TODO: 後に機能追加 — 実績バッジ獲得数
            // ->withCount('studentBadges')
            ->orderBy('id');

        $excludeUserId = $options['excludeUserId'] ?? null;
        if ($excludeUserId !== null) {
            $query->whereKeyNot($excludeUserId);
        }

        $search = trim((string) $search);
        if ($search !== '') {
            if ($options['searchNameOnly'] ?? false) {
                $query->where('name', 'like', '%'.$search.'%');
            } else {
                $query->where(function (Builder $builder) use ($search): void {
                    $builder
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            }
        }

        return $query;
    }
}
