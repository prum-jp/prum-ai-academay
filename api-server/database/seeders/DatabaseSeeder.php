<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'mentor@prum.local'],
            [
                'name' => 'メンター太郎',
                'password' => 'password',
                'role' => User::ROLE_MENTOR,
            ],
        );

        $students = [
            ['email' => 'student@prum.local', 'name' => '勇者ぷるむ'],
            ['email' => 'hana@prum.local', 'name' => '花咲みらい'],
            ['email' => 'kenta@prum.local', 'name' => '剣持ケンタ'],
            ['email' => 'sora@prum.local', 'name' => '空野ソラ'],
            ['email' => 'riku@prum.local', 'name' => '陸奥リク'],
            ['email' => 'yui@prum.local', 'name' => '結城ユイ'],
        ];

        foreach ($students as $student) {
            User::query()->updateOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => 'password',
                    'role' => User::ROLE_STUDENT,
                ],
            );
        }

        $this->call([
            StudentDataSeeder::class,
            ToolSeeder::class,
            BadgeSeeder::class,
        ]);
    }
}
