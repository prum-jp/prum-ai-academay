<?php

namespace App\Services;

use App\Support\QuestDescriptionSections;
use Illuminate\Support\Collection;

class MentorQuestMasterCsvExporter
{
    /**
     * @var list<string>
     */
    private const HEADERS = [
        'No',
        'Unit',
        'Quest',
        'To do',
        'Unit名',
        'Quest名',
        '内容',
        '目的',
        '完了条件',
        'ツール',
        '難度',
        'XP',
        'クエスト段階',
    ];

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     */
    public function export(Collection $rows): string
    {
        $lines = [implode(',', array_map($this->escapeCell(...), self::HEADERS))];

        foreach ($rows as $row) {
            $lines[] = implode(',', array_map($this->escapeCell(...), [
                (string) ($row['csvNo'] ?? ''),
                (string) ($row['unitSortOrder'] ?? ''),
                (string) ($row['questNo'] ?? ''),
                (string) ($row['todoNote'] ?? ''),
                (string) ($row['unitTitle'] ?? ''),
                (string) ($row['title'] ?? ''),
                (string) ($row['content'] ?? ''),
                (string) ($row['purpose'] ?? ''),
                (string) ($row['clearCondition'] ?? ''),
                (string) ($row['toolName'] ?? ''),
                (string) ($row['difficulty'] ?? ''),
                (string) ($row['experiencePoints'] ?? ''),
                (string) ($row['questTierLabel'] ?? ''),
            ]));
        }

        return "\xEF\xBB\xBF".implode("\n", $lines);
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapChildQuestRow(array $row): array
    {
        $split = QuestDescriptionSections::splitForCsvExport((string) ($row['description'] ?? ''));

        return [
            'csvNo' => $row['sortOrder'] ?? '',
            'unitSortOrder' => $row['unitSortOrder'] ?? '',
            'questNo' => $row['sortOrder'] ?? '',
            'todoNote' => '',
            'unitTitle' => $row['unitTitle'] ?? '',
            'title' => $row['title'] ?? '',
            'content' => $split['content'],
            'purpose' => $split['purpose'],
            'clearCondition' => $row['clearCondition'] ?? '',
            'toolName' => $row['toolCode'] ?? '',
            'difficulty' => $row['difficulty'] ?? '',
            'experiencePoints' => $row['experiencePoints'] ?? '',
            'questTierLabel' => $row['questTierLabel'] ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    public function mapUnitOnlyRow(array $row): array
    {
        return [
            'csvNo' => '',
            'unitSortOrder' => $row['sortOrder'] ?? '',
            'questNo' => '',
            'todoNote' => '',
            'unitTitle' => $row['title'] ?? '',
            'title' => '',
            'content' => $row['description'] ?? '',
            'purpose' => '',
            'clearCondition' => '',
            'toolName' => '',
            'difficulty' => '',
            'experiencePoints' => '',
            'questTierLabel' => '',
        ];
    }

    private function escapeCell(mixed $value): string
    {
        $text = (string) $value;
        if ($text === '') {
            return '';
        }

        if (str_contains($text, '"') || str_contains($text, ',') || str_contains($text, "\n") || str_contains($text, "\r")) {
            return '"'.str_replace('"', '""', $text).'"';
        }

        return $text;
    }
}
