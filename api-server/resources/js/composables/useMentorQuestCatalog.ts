import { onMounted, reactive } from 'vue';
import {
    fetchMentorQuests,
    fetchMentorQuestUnits,
    publishMentorQuest,
    publishMentorQuestUnit,
} from '@/api/questAdmin';
import { mentorQuestAdminMessages } from '@/constants/questAdmin';
import type { MentorQuestItem, MentorQuestUnitItem } from '@/types/questAdmin';
import type { QuestListMeta } from '@/types/quest';

interface MentorUnitSectionState {
    units: MentorQuestUnitItem[];
    meta: QuestListMeta | null;
    isLoading: boolean;
    error: string;
}

interface MentorQuestSectionState {
    quests: MentorQuestItem[];
    meta: QuestListMeta | null;
    isLoading: boolean;
    error: string;
}

const createUnitSectionState = (): MentorUnitSectionState => ({
    units: [],
    meta: null,
    isLoading: true,
    error: '',
});

const createQuestSectionState = (): MentorQuestSectionState => ({
    quests: [],
    meta: null,
    isLoading: true,
    error: '',
});

export function useMentorQuestCatalog() {
    const personalUnits = reactive(createUnitSectionState());
    const sections = reactive({
        team: createQuestSectionState(),
        special: createQuestSectionState(),
    });

    const loadPersonalUnits = async (page = 1): Promise<void> => {
        personalUnits.isLoading = true;
        personalUnits.error = '';

        try {
            const response = await fetchMentorQuestUnits(page);
            personalUnits.units = response.data;
            personalUnits.meta = response.meta;
        } catch {
            personalUnits.error = mentorQuestAdminMessages.loadUnitsFailed;
            personalUnits.units = [];
            personalUnits.meta = null;
        } finally {
            personalUnits.isLoading = false;
        }
    };

    const loadSection = async (type: 'team' | 'special', page = 1): Promise<void> => {
        const section = sections[type];
        section.isLoading = true;
        section.error = '';

        try {
            const response = await fetchMentorQuests(type, page);
            section.quests = response.data;
            section.meta = response.meta;
        } catch {
            section.error = mentorQuestAdminMessages.loadQuestsFailed;
            section.quests = [];
            section.meta = null;
        } finally {
            section.isLoading = false;
        }
    };

    const setUnitPublished = async (
        unit: MentorQuestUnitItem,
        isPublished: boolean,
    ): Promise<boolean> => {
        try {
            const updated = await publishMentorQuestUnit(unit.id, isPublished);
            const target = personalUnits.units.find((item) => item.id === unit.id);
            if (target) {
                target.isPublished = updated.isPublished;
            }
            return true;
        } catch {
            return false;
        }
    };

    const setQuestPublished = async (
        type: 'team' | 'special',
        quest: MentorQuestItem,
        isPublished: boolean,
    ): Promise<boolean> => {
        try {
            const updated = await publishMentorQuest(quest.id, isPublished);
            const target = sections[type].quests.find((item) => item.id === quest.id);
            if (target) {
                target.isPublished = updated.isPublished;
            }
            return true;
        } catch {
            return false;
        }
    };

    const reloadAll = (): void => {
        void loadPersonalUnits(personalUnits.meta?.currentPage ?? 1);
        void loadSection('team', sections.team.meta?.currentPage ?? 1);
        void loadSection('special', sections.special.meta?.currentPage ?? 1);
    };

    onMounted(() => {
        void loadPersonalUnits();
        void loadSection('team');
        void loadSection('special');
    });

    return {
        personalUnits,
        sections,
        loadPersonalUnits,
        loadSection,
        setUnitPublished,
        setQuestPublished,
        reloadAll,
    };
}
