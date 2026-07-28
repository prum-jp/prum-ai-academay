<?php

namespace Database\Seeders;

use App\Models\Badge;
use App\Models\Quest;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $questIdByTitle = Quest::query()
            ->pluck('id', 'title');

        $badges = [
            [
                'code' => 'basic_mark',
                'title' => '基礎の印',
                'description' => 'プロンプト基礎修行をクリアした証',
                'icon' => 'fa-solid fa-wand-magic-sparkles',
                'unlock_quest_title' => '個人：プロンプト基礎修行をクリア',
                'sort_order' => 1,
            ],
            [
                'code' => 'summary_spell',
                'title' => '要約の呪文',
                'description' => 'AI議事録を現場で使った証',
                'icon' => 'fa-solid fa-file-audio',
                'unlock_quest_title' => '個人：AI議事録を現場で1回使う',
                'sort_order' => 2,
            ],
            [
                'code' => 'team_bond',
                'title' => 'チーム結束',
                'description' => 'チームクエストを達成した証',
                'icon' => 'fa-solid fa-users',
                'unlock_quest_title' => 'チーム：業務効率化ツールを共同作成する',
                'sort_order' => 3,
            ],
            [
                'code' => 'hackathon',
                'title' => 'ハッカソン',
                'description' => 'ハッカソンで成果を発表した証',
                'icon' => 'fa-solid fa-trophy',
                'unlock_quest_title' => '特別：当日ハッカソンで成果を発表する',
                'sort_order' => 4,
            ],
            [
                'code' => 'explorer',
                'title' => '探検家',
                'description' => '課題発見ワークショップを達成した証',
                'icon' => 'fa-solid fa-compass',
                'unlock_quest_title' => 'チーム：課題発見ワークショップ',
                'sort_order' => 5,
            ],
            [
                'code' => 'sage',
                'title' => '爆速賢者',
                'description' => '成果物をギルド掲示板に投稿した証',
                'icon' => 'fa-solid fa-crown',
                'unlock_quest_title' => '特別：成果物をギルド掲示板に投稿する',
                'sort_order' => 6,
            ],
            [
                'code' => 'slack_herald',
                'title' => '伝令の翼',
                'description' => '冒険者カードをSlackに投稿した証',
                'icon' => 'fa-brands fa-slack',
                'unlock_quest_title' => '個人：冒険者カードをSlackに投稿する',
                'sort_order' => 7,
            ],
            [
                'code' => 'diary_scribe',
                'title' => '学びの記録者',
                'description' => '学びを1行日記に残した証',
                'icon' => 'fa-solid fa-pen-to-square',
                'unlock_quest_title' => '個人：学びを1行日記に残す',
                'sort_order' => 8,
            ],
            [
                'code' => 'retro_host',
                'title' => 'ふりかえり長',
                'description' => '週次ふりかえり会を主催した証',
                'icon' => 'fa-solid fa-comments',
                'unlock_quest_title' => 'チーム：週次ふりかえり会を主催する',
                'sort_order' => 9,
            ],
            [
                'code' => 'prompt_guild',
                'title' => '呪文共有団',
                'description' => 'プロンプト共有会を開いた証',
                'icon' => 'fa-solid fa-book-open',
                'unlock_quest_title' => 'チーム：プロンプト共有会を開く',
                'sort_order' => 10,
            ],
            [
                'code' => 'study_speaker',
                'title' => '語りの賢者',
                'description' => '社内勉強会でAI活用事例を話した証',
                'icon' => 'fa-solid fa-chalkboard-user',
                'unlock_quest_title' => '特別：社内勉強会でAI活用事例を話す',
                'sort_order' => 11,
            ],
            [
                'code' => 'helper_stamp',
                'title' => 'お助けの証',
                'description' => 'メンターへお助けスタンプを贈った証',
                'icon' => 'fa-solid fa-handshake-angle',
                'unlock_quest_title' => '特別：メンターへお助けスタンプを贈る',
                'sort_order' => 12,
            ],
        ];

        foreach ($badges as $badge) {
            $questTitle = $badge['unlock_quest_title'];
            unset($badge['unlock_quest_title']);

            Badge::query()->updateOrCreate(
                ['code' => $badge['code']],
                [
                    ...$badge,
                    'unlock_type' => Badge::UNLOCK_QUEST_COMPLETE,
                    'unlock_quest_id' => $questIdByTitle[$questTitle] ?? null,
                ],
            );
        }
    }
}
