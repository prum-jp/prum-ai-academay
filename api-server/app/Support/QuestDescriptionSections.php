<?php

namespace App\Support;

class QuestDescriptionSections
{
    private const PURPOSE_MARKER = '【目的】';

    private const PROCEDURE_MARKER = '【内容・進め方】';

    private const DELIVERABLE_MARKER = '【提出物】';

    /**
     * @return array{overview: string, purpose: string, deliverable: string, completionCondition: string}
     */
    public static function parse(string $description, string $clearCondition): array
    {
        $parsedDescription = self::parseDescriptionBody($description);
        $parsedClear = self::splitDeliverable($clearCondition);

        return [
            'overview' => $parsedDescription['overview'],
            'purpose' => $parsedDescription['purpose'],
            'deliverable' => $parsedClear['deliverable'],
            'completionCondition' => $parsedClear['completionCondition'],
        ];
    }

    public static function sameContent(
        string $descriptionLeft,
        string $clearLeft,
        string $descriptionRight,
        string $clearRight,
    ): bool {
        $left = self::parse($descriptionLeft, $clearLeft);
        $right = self::parse($descriptionRight, $clearRight);

        foreach (['overview', 'purpose', 'deliverable', 'completionCondition'] as $key) {
            if (trim($left[$key]) !== trim($right[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, array{incoming: string, existing: string}>
     */
    public static function diffSections(
        string $incomingDescription,
        string $incomingClearCondition,
        string $existingDescription,
        string $existingClearCondition,
    ): array {
        $incoming = self::parse($incomingDescription, $incomingClearCondition);
        $existing = self::parse($existingDescription, $existingClearCondition);
        $diffs = [];

        foreach (['overview', 'purpose', 'deliverable', 'completionCondition'] as $key) {
            $incomingText = trim($incoming[$key]);
            $existingText = trim($existing[$key]);

            if ($incomingText !== $existingText) {
                $diffs[$key] = [
                    'incoming' => $incomingText,
                    'existing' => $existingText,
                ];
            }
        }

        return $diffs;
    }

    /**
     * @return array{overview: string, purpose: string}
     */
    private static function parseDescriptionBody(string $description): array
    {
        $text = $description;
        $purposeIndex = mb_strpos($text, self::PURPOSE_MARKER);
        $procedureIndex = mb_strpos($text, self::PROCEDURE_MARKER);

        if ($purposeIndex === false && $procedureIndex === false) {
            return [
                'overview' => trim($text),
                'purpose' => '',
            ];
        }

        $markers = array_values(array_filter([
            $purposeIndex !== false ? ['index' => $purposeIndex, 'marker' => self::PURPOSE_MARKER, 'key' => 'purpose'] : null,
            $procedureIndex !== false ? ['index' => $procedureIndex, 'marker' => self::PROCEDURE_MARKER, 'key' => 'procedure'] : null,
        ], fn (?array $item): bool => $item !== null));

        usort($markers, fn (array $left, array $right): int => $left['index'] <=> $right['index']);

        $overview = trim(mb_substr($text, 0, $markers[0]['index']));
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

        return [
            'overview' => self::mergeOverviewWithLegacyProcedure($overview, $procedure),
            'purpose' => $purpose,
        ];
    }

    /**
     * @return array{completionCondition: string, deliverable: string}
     */
    private static function splitDeliverable(string $text): array
    {
        $index = mb_strpos($text, self::DELIVERABLE_MARKER);

        if ($index === false) {
            return [
                'completionCondition' => trim($text),
                'deliverable' => '',
            ];
        }

        return [
            'completionCondition' => trim(mb_substr($text, 0, $index)),
            'deliverable' => trim(mb_substr($text, $index + mb_strlen(self::DELIVERABLE_MARKER))),
        ];
    }

    private static function mergeOverviewWithLegacyProcedure(string $overview, string $procedure): string
    {
        $overviewText = trim($overview);
        $procedureText = trim($procedure);

        if ($procedureText === '') {
            return $overviewText;
        }

        if ($overviewText === '') {
            return $procedureText;
        }

        return $overviewText."\n\n".$procedureText;
    }

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
