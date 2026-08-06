<?php

namespace App\Support;

class QuestDescriptionSections
{
    private const PURPOSE_MARKER = '【目的】';

    private const PROCEDURE_MARKER = '【内容・進め方】';

    /**
     * @return array{content: string, purpose: string}
     */
    public static function splitForCsvExport(string $description): array
    {
        $text = trim($description);
        if ($text === '') {
            return ['content' => '', 'purpose' => ''];
        }

        $purposeIndex = mb_strpos($text, self::PURPOSE_MARKER);
        $procedureIndex = mb_strpos($text, self::PROCEDURE_MARKER);

        if ($purposeIndex === false && $procedureIndex === false) {
            return ['content' => $text, 'purpose' => ''];
        }

        $markers = array_values(array_filter([
            $purposeIndex !== false ? ['index' => $purposeIndex, 'marker' => self::PURPOSE_MARKER, 'key' => 'purpose'] : null,
            $procedureIndex !== false ? ['index' => $procedureIndex, 'marker' => self::PROCEDURE_MARKER, 'key' => 'procedure'] : null,
        ], fn (?array $item): bool => $item !== null));

        usort($markers, fn (array $left, array $right): int => $left['index'] <=> $right['index']);

        $content = trim(mb_substr($text, 0, $markers[0]['index']));
        $purpose = '';
        $procedure = '';

        foreach ($markers as $index => $marker) {
            $contentStart = $marker['index'] + mb_strlen($marker['marker']);
            $contentEnd = $index + 1 < count($markers)
                ? $markers[$index + 1]['index']
                : mb_strlen($text);
            $sectionContent = trim(mb_substr($text, $contentStart, $contentEnd - $contentStart));

            if ($marker['key'] === 'purpose') {
                $purpose = $sectionContent;
            } else {
                $procedure = $sectionContent;
            }
        }

        $parts = array_values(array_filter([
            $content !== '' ? $content : null,
            $procedure !== '' ? $procedure : null,
        ]));

        return [
            'content' => implode("\n\n", $parts),
            'purpose' => $purpose,
        ];
    }
}
