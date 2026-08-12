import { reactive } from 'vue';
import { fetchQuests } from '@/api/quest/quests';
import { questMessages, type NonPersonalQuestType } from '@/constants/quest/quests';
import type { QuestItem, QuestListMeta } from '@/types/quest/quest';

export interface QuestSectionState {
    quests: QuestItem[];
    meta: QuestListMeta | null;
    isLoading: boolean;
    error: string;
}

const createSectionState = (): QuestSectionState => ({
    quests: [],
    meta: null,
    isLoading: true,
    error: '',
});

export function useQuestSections() {
    const sections = reactive<Record<NonPersonalQuestType, QuestSectionState>>({
        team: createSectionState(),
        special: createSectionState(),
    });

    const loadSection = async (type: NonPersonalQuestType, page = 1): Promise<void> => {
        const section = sections[type];
        section.isLoading = true;
        section.error = '';

        try {
            const response = await fetchQuests(type, page);
            section.quests = response.data;
            section.meta = response.meta;
        } catch {
            section.error = questMessages.loadQuestsFailed;
            section.quests = [];
            section.meta = null;
        } finally {
            section.isLoading = false;
        }
    };

    const applyQuestUpdate = (type: NonPersonalQuestType, updated: QuestItem): void => {
        sections[type].quests = sections[type].quests.map((quest) =>
            quest.id === updated.id ? updated : quest,
        );
    };

    const loadAllSections = (): void => {
        const types: NonPersonalQuestType[] = ['team', 'special'];
        for (const type of types) {
            void loadSection(type);
        }
    };

    return {
        sections,
        loadSection,
        loadAllSections,
        applyQuestUpdate,
    };
}
