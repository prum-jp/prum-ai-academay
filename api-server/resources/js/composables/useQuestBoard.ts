import { onMounted, ref } from 'vue';
import {
    nonPersonalSectionDefinitions,
    personalSectionDefinition,
} from '@/constants/quests';
import { useQuestSections } from '@/composables/useQuestSections';
import { useQuestUnits } from '@/composables/useQuestUnits';
import type { QuestItem, QuestUnitItem } from '@/types/quest';
import { unitContainsQuest, updateUnitQuest } from '@/utils/questUnitProgress';

export function useQuestBoard() {
    const { personalUnits, loadPersonalUnits, setPersonalProgressFilter, personalUnitsEmptyMessage, applyQuestUpdate } =
        useQuestUnits();
    const { sections, loadSection, loadAllSections, applyQuestUpdate: applySectionQuestUpdate } =
        useQuestSections();

    const selectedQuest = ref<QuestItem | null>(null);
    const selectedUnit = ref<QuestUnitItem | null>(null);

    const syncSelectedQuest = (updated: QuestItem): void => {
        if (selectedQuest.value?.id === updated.id) {
            selectedQuest.value = updated;
        }

        if (selectedUnit.value && unitContainsQuest(selectedUnit.value, updated.id)) {
            selectedUnit.value = updateUnitQuest(selectedUnit.value, updated);
        }
    };

    const applyQuestUpdateEverywhere = (updated: QuestItem): void => {
        applyQuestUpdate(updated);

        for (const definition of nonPersonalSectionDefinitions) {
            applySectionQuestUpdate(definition.type, updated);
        }

        syncSelectedQuest(updated);
    };

    const openDetail = (quest: QuestItem): void => {
        selectedQuest.value = quest;
    };

    const openUnit = (unit: QuestUnitItem): void => {
        selectedUnit.value = unit;
    };

    const closeDetail = (): void => {
        selectedQuest.value = null;
    };

    const closeUnit = (): void => {
        selectedUnit.value = null;
    };

    onMounted(() => {
        void loadPersonalUnits();
        loadAllSections();
    });

    return {
        questSectionDefinitions: nonPersonalSectionDefinitions,
        personalDefinition: personalSectionDefinition,
        personalUnits,
        sections,
        selectedQuest,
        selectedUnit,
        loadPersonalUnits,
        setPersonalProgressFilter,
        personalUnitsEmptyMessage,
        loadSection,
        applyQuestUpdateEverywhere,
        openDetail,
        openUnit,
        closeDetail,
        closeUnit,
    };
}
