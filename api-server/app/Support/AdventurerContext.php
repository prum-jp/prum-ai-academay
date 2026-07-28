<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AdventurerContext
{
    public const SESSION_TARGET_STUDENT_ID = 'mentor_target_student_id';

    /**
     * 学習者は自分自身、メンターは選択中（未選択時は先頭）の学習者を返す。
     */
    public static function targetStudent(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->isStudent()) {
            return $user;
        }

        if ($user->isMentor()) {
            $student = self::resolveMentorTarget($request);

            if ($student === null) {
                throw new HttpException(404, '学習者が見つかりません。');
            }

            return $student;
        }

        throw new HttpException(403, '学習者またはメンターのみアクセスできます。');
    }

    public static function resolveMentorTarget(Request $request): ?User
    {
        $selectedId = $request->session()->get(self::SESSION_TARGET_STUDENT_ID);

        if ($selectedId !== null) {
            $selected = User::query()
                ->where('role', User::ROLE_STUDENT)
                ->whereKey($selectedId)
                ->first();

            if ($selected !== null) {
                return $selected;
            }
        }

        return User::query()
            ->where('role', User::ROLE_STUDENT)
            ->orderBy('id')
            ->first();
    }

    public static function setMentorTarget(Request $request, User $student): void
    {
        $request->session()->put(self::SESSION_TARGET_STUDENT_ID, $student->id);
    }

    public static function clearMentorTarget(Request $request): void
    {
        $request->session()->forget(self::SESSION_TARGET_STUDENT_ID);
    }
}
