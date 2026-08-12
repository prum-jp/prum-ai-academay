import type { QuestImportApplyResult } from '@/types/mentor-quest/questImport';

export const collectPersonalUnitIdsFromImportResults = (
    results: QuestImportApplyResult[],
): number[] => {
    const unitIds = new Set<number>();

    for (const result of results) {
        if (result.kind === 'personal_unit' && typeof result.id === 'number') {
            unitIds.add(result.id);
        }

        if (result.kind === 'child_quest' && typeof result.unitId === 'number') {
            unitIds.add(result.unitId);
        }
    }

    return [...unitIds];
};
