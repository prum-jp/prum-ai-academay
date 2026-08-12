import { reactive } from 'vue';
import { fetchQuestUnits } from '@/api/quest/quests';
import { questMessages } from '@/constants/quest/quests';
import {
    DEFAULT_UNIT_PROGRESS_FILTER,
    type UnitProgressFilter,
} from '@/constants/quest/unitProgress';
import type { QuestItem, QuestListMeta, QuestUnitItem } from '@/types/quest/quest';
import { unitContainsQuest, updateUnitQuest } from '@/utils/quest/questUnitProgress';

export interface PersonalUnitSectionState {
    units: QuestUnitItem[];
    meta: QuestListMeta | null;
    progressFilter: UnitProgressFilter;
    isLoading: boolean;
    error: string;
}

const createPersonalUnitState = (): PersonalUnitSectionState => ({
    units: [],
    meta: null,
    progressFilter: DEFAULT_UNIT_PROGRESS_FILTER,
    isLoading: true,
    error: '',
});

export function useQuestUnits() {
    const personalUnits = reactive(createPersonalUnitState());

    const loadPersonalUnits = async (page = 1): Promise<void> => {
        personalUnits.isLoading = true;
        personalUnits.error = '';

        try {
            const response = await fetchQuestUnits(page, personalUnits.progressFilter);
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

    const setPersonalProgressFilter = (filter: UnitProgressFilter): void => {
        personalUnits.progressFilter = filter;
        void loadPersonalUnits(1);
    };

    const personalUnitsEmptyMessage = (): string =>
        personalUnits.progressFilter === DEFAULT_UNIT_PROGRESS_FILTER
            ? questMessages.emptyUnits
            : questMessages.emptyFilteredUnits;

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
        setPersonalProgressFilter,
        personalUnitsEmptyMessage,
        applyQuestUpdate,
        applyQuestUpdates,
    };
}
