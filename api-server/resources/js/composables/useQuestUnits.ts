import { reactive } from 'vue';
import { fetchQuestUnits } from '@/api/quests';
import { questMessages } from '@/constants/quests';
import type { QuestItem, QuestListMeta, QuestUnitItem } from '@/types/quest';
import { unitContainsQuest, updateUnitQuest } from '@/utils/questUnitProgress';

export interface PersonalUnitSectionState {
    units: QuestUnitItem[];
    meta: QuestListMeta | null;
    isLoading: boolean;
    error: string;
}

const createPersonalUnitState = (): PersonalUnitSectionState => ({
    units: [],
    meta: null,
    isLoading: true,
    error: '',
});

export function useQuestUnits() {
    const personalUnits = reactive(createPersonalUnitState());

    const loadPersonalUnits = async (page = 1): Promise<void> => {
        personalUnits.isLoading = true;
        personalUnits.error = '';

        try {
            const response = await fetchQuestUnits(page);
            personalUnits.units = response.data;
            personalUnits.meta = response.meta;
        } catch {
            personalUnits.error = questMessages.loadUnitsFailed;
            personalUnits.units = [];
            personalUnits.meta = null;
        } finally {
            personalUnits.isLoading = false;
        }
    };

    const applyQuestUpdate = (updated: QuestItem): void => {
        personalUnits.units = personalUnits.units.map((unit) =>
            unitContainsQuest(unit, updated.id) ? updateUnitQuest(unit, updated) : unit,
        );
    };

    const applyQuestUpdates = (updatedQuests: QuestItem[]): void => {
        for (const updated of updatedQuests) {
            applyQuestUpdate(updated);
        }
    };

    return {
        personalUnits,
        loadPersonalUnits,
        applyQuestUpdate,
        applyQuestUpdates,
    };
}
