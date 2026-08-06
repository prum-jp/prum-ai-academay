<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\StudentStat;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MentorStudentRegistrar
{
    /**
     * @param  array{name: string, email: string, password: string, role: int}  $payload
     */
    public function register(array $payload): User
    {
        return DB::transaction(function () use ($payload): User {
            $role = (int) $payload['role'];

            $user = User::query()->create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'role' => $role,
            ]);

            if ($role === User::ROLE_STUDENT) {
                StudentProfile::query()->create([
                    'user_id' => $user->id,
                    'background' => '',
                    'hobby' => '',
                    'weapon_skill' => '',
                    'spell_goal' => '',
                ]);

                StudentStat::query()->create([
                    'user_id' => $user->id,
                    'stat_business_skill' => 0,
                    'stat_human_skill' => 0,
                    'stat_conceptual_skill' => 0,
                    'total_xp' => 0,
                ]);

                // TODO: 後に機能追加 — 実績バッジ獲得数
                // return $user->load(['studentProfile', 'studentStat'])->loadCount('studentBadges');
                return $user->load(['studentProfile', 'studentStat']);
            }

            // TODO: 後に機能追加 — 実績バッジ獲得数
            // return $user->loadCount('studentBadges');
            return $user;
        });
    }
}
