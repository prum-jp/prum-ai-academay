import type { QuestImportItem } from '@/types/mentor-quest/questImport';
import { compareQuestSort, compareUnitSort } from '@/utils/mentor-quest/questImport/sortComparators';

/** ユニット → 紐づくクエスト の順に並べ替える */
export const groupImportItemsByUnit = (items: QuestImportItem[]): QuestImportItem[] => {
    const units = items.filter((item) => item.kind === 'personal_unit');
    const quests = items.filter((item) => item.kind === 'child_quest');
    const others = items.filter(
        (item) => item.kind !== 'personal_unit' && item.kind !== 'child_quest',
    );

    const questsByUnitTitle = new Map<string, QuestImportItem[]>();

    for (const quest of quests) {
        const unitTitle = quest.unitTitle ?? '';
        const unitQuests = questsByUnitTitle.get(unitTitle) ?? [];
        unitQuests.push(quest);
        questsByUnitTitle.set(unitTitle, unitQuests);
    }

    for (const unitQuests of questsByUnitTitle.values()) {
        unitQuests.sort(compareQuestSort);
    }

    const grouped: QuestImportItem[] = [...others];

    for (const unit of [...units].sort(compareUnitSort)) {
        grouped.push(unit);
        const unitQuests = questsByUnitTitle.get(unit.title) ?? [];
        grouped.push(...unitQuests);
        questsByUnitTitle.delete(unit.title);
    }

    for (const orphanQuests of questsByUnitTitle.values()) {
        grouped.push(...orphanQuests);
    }

    return grouped;
};
