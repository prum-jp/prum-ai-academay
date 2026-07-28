<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\StudentStat;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentDataSeeder extends Seeder
{
    /**
     * アカデミー生（role=0）向けのプロフィール・ステータスを投入する。
     *
     * @var array<string, array{background: string, hobby: string, weapon_skill: string, spell_goal: string, stats: array<string, int>}>
     */
    private const STUDENT_PRESETS = [
        'student@prum.local' => [
            'background' => '住宅営業職（交渉が得意）',
            'hobby' => 'サウナ / カフェ巡り',
            'weapon_skill' => 'Excelの単純集計作業、クライアントとのアポ調整',
            'spell_goal' => 'プロンプトで会議アジェンダを作る呪文、自動議事録の魔法',
            'stats' => [
                'stat_presentation' => 2,
                'stat_communication' => 3,
                'stat_problem_finding' => 1,
                'stat_ai_affinity' => 2,
                'stat_action' => 2,
                'stat_support' => 1,
            ],
        ],
        'hana@prum.local' => [
            'background' => 'カスタマーサポート（傾聴力）',
            'hobby' => '読書 / ヨガ',
            'weapon_skill' => '問い合わせ対応、FAQ整備',
            'spell_goal' => 'チャットボットで一次対応を自動化する呪文',
            'stats' => [
                'stat_presentation' => 1,
                'stat_communication' => 4,
                'stat_problem_finding' => 2,
                'stat_ai_affinity' => 1,
                'stat_action' => 2,
                'stat_support' => 3,
            ],
        ],
        'kenta@prum.local' => [
            'background' => '製造現場の改善リーダー',
            'hobby' => '筋トレ / キャンプ',
            'weapon_skill' => '現場改善提案、作業標準書づくり',
            'spell_goal' => '画像認識で検品を速くする魔法',
            'stats' => [
                'stat_presentation' => 2,
                'stat_communication' => 2,
                'stat_problem_finding' => 4,
                'stat_ai_affinity' => 1,
                'stat_action' => 4,
                'stat_support' => 1,
            ],
        ],
        'sora@prum.local' => [
            'background' => 'マーケター（SNS運用）',
            'hobby' => '写真 / カフェ巡り',
            'weapon_skill' => '投稿企画、簡易バナー作成',
            'spell_goal' => '生成AIでキャッチコピーを量産する呪文',
            'stats' => [
                'stat_presentation' => 3,
                'stat_communication' => 3,
                'stat_problem_finding' => 2,
                'stat_ai_affinity' => 3,
                'stat_action' => 2,
                'stat_support' => 1,
            ],
        ],
        'riku@prum.local' => [
            'background' => '経理アシスタント',
            'hobby' => '将棋 / ランニング',
            'weapon_skill' => '請求書チェック、月次集計',
            'spell_goal' => '表計算とAIで仕訳候補を出す魔法',
            'stats' => [
                'stat_presentation' => 1,
                'stat_communication' => 2,
                'stat_problem_finding' => 3,
                'stat_ai_affinity' => 2,
                'stat_action' => 2,
                'stat_support' => 2,
            ],
        ],
        'yui@prum.local' => [
            'background' => '新卒バックオフィス',
            'hobby' => 'ゲーム / アニメ',
            'weapon_skill' => 'スケジュール調整、議事メモ',
            'spell_goal' => '議事録要約とToDo抽出の呪文',
            'stats' => [
                'stat_presentation' => 1,
                'stat_communication' => 2,
                'stat_problem_finding' => 1,
                'stat_ai_affinity' => 3,
                'stat_action' => 3,
                'stat_support' => 2,
            ],
        ],
    ];

    public function run(): void
    {
        $students = User::query()
            ->where('role', User::ROLE_STUDENT)
            ->get();

        foreach ($students as $student) {
            $preset = self::STUDENT_PRESETS[$student->email] ?? self::STUDENT_PRESETS['student@prum.local'];

            StudentProfile::query()->updateOrCreate(
                ['user_id' => $student->id],
                [
                    'background' => $preset['background'],
                    'hobby' => $preset['hobby'],
                    'weapon_skill' => $preset['weapon_skill'],
                    'spell_goal' => $preset['spell_goal'],
                ],
            );

            StudentStat::query()->updateOrCreate(
                ['user_id' => $student->id],
                $preset['stats'],
            );
        }
    }
}
