import { onMounted, ref } from 'vue';
import {
    nonPersonalSectionDefinitions,
    personalSectionDefinition,
} from '@/constants/quests';
import { useQuestSections } from '@/composables/useQuestSections';
import { useQuestToggle } from '@/composables/useQuestToggle';
import { useQuestUnits } from '@/composables/useQuestUnits';
import type { QuestItem, QuestUnitItem } from '@/types/quest';
import type { NonPersonalQuestType } from '@/constants/quests';
import {
    getQuestsToToggleForUnit,
    unitContainsQuest,
    updateUnitQuest,
} from '@/utils/questUnitProgress';

export function useQuestBoard() {
    const { personalUnits, loadPersonalUnits, applyQuestUpdate, applyQuestUpdates } =
        useQuestUnits();
    const { sections, loadSection, loadAllSections, applyQuestUpdate: applySectionQuestUpdate } =
        useQuestSections();
    const { isUpdating, toggleQuest, toggleQuests, playSound } = useQuestToggle();

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

    const applyQuestUpdateEverywhere = (
        updated: QuestItem,
        sectionType?: NonPersonalQuestType,
    ): void => {
        if (sectionType) {
            applySectionQuestUpdate(sectionType, updated);
        } else {
            applyQuestUpdate(updated);
        }

        syncSelectedQuest(updated);
    };

    const handleToggle = async (
        type: NonPersonalQuestType,
        quest: QuestItem,
    ): Promise<void> => {
        const updated = await toggleQuest(quest);
        if (!updated) {
            return;
        }

        applyQuestUpdateEverywhere(updated, type);
    };

    const handlePersonalToggle = async (quest: QuestItem): Promise<void> => {
        const updated = await toggleQuest(quest);
        if (!updated) {
            return;
        }

        applyQuestUpdateEverywhere(updated);
    };

    const handleUnitToggle = async (unit: QuestUnitItem): Promise<void> => {
        if (isUpdating.value || unit.quests.some((quest) => quest.isLocked)) {
            playSound('down');
            return;
        }

        const { quests } = getQuestsToToggleForUnit(unit);
        if (quests.length === 0) {
            return;
        }

        const updatedQuests = await toggleQuests(quests);
        if (updatedQuests.length === 0) {
            return;
        }

        applyQuestUpdates(updatedQuests);
        for (const updated of updatedQuests) {
            syncSelectedQuest(updated);
        }
    };

    const openDetail = (quest: QuestItem): void => {
        selectedQuest.value = quest;
        playSound('click');
    };

    const openUnit = (unit: QuestUnitItem): void => {
        selectedUnit.value = unit;
        playSound('click');
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
        isUpdating,
        selectedQuest,
        selectedUnit,
        loadPersonalUnits,
        loadSection,
        handleToggle,
        handlePersonalToggle,
        handleUnitToggle,
        openDetail,
        openUnit,
        closeDetail,
        closeUnit,
    };
}
