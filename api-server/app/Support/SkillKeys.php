<?php

namespace App\Support;

class SkillKeys
{
    public const BUSINESS = 'businessSkill';

    public const HUMAN = 'humanSkill';

    public const CONCEPTUAL = 'conceptualSkill';

    /**
     * @var list<string>
     */
    public const ALL = [
        self::BUSINESS,
        self::HUMAN,
        self::CONCEPTUAL,
    ];

    /**
     * @var array<string, string>
     */
    public const COLUMN_MAP = [
        self::BUSINESS => 'stat_business_skill',
        self::HUMAN => 'stat_human_skill',
        self::CONCEPTUAL => 'stat_conceptual_skill',
    ];

    /**
     * @var array<string, string>
     */
    public const LABELS = [
        self::BUSINESS => 'ビジネススキル',
        self::HUMAN => 'ヒューマンスキル',
        self::CONCEPTUAL => 'コンセプチュアルスキル',
    ];

    /**
     * @param  list<string>  $skills
     * @return list<string>
     */
    public static function normalizeList(array $skills): array
    {
        $normalized = [];

        foreach ($skills as $skill) {
            if (! is_string($skill)) {
                continue;
            }

            $skill = trim($skill);
            if ($skill !== '' && in_array($skill, self::ALL, true)) {
                $normalized[] = $skill;
            }
        }

        return array_values(array_unique($normalized));
    }
}
