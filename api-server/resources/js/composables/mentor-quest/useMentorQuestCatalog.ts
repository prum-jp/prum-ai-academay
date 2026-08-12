import { onMounted, reactive } from 'vue';
import { fetchMentorQuests, publishMentorQuest } from '@/api/mentor-quest/questAdmin';
import { mentorQuestAdminMessages } from '@/constants/mentor-quest/questAdmin';
import type { MentorQuestItem } from '@/types/mentor-quest/questAdmin';
import type { QuestListMeta } from '@/types/quest/quest';

interface MentorQuestSectionState {
    quests: MentorQuestItem[];
    meta: QuestListMeta | null;
    isLoading: boolean;
    error: string;
}

const createQuestSectionState = (): MentorQuestSectionState => ({
    quests: [],
    meta: null,
    isLoading: true,
    error: '',
});

export function useMentorQuestCatalog() {
    const sections = reactive({
        team: createQuestSectionState(),
        special: createQuestSectionState(),
    });

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

    const reloadTeamSections = (): void => {
        void loadSection('team', sections.team.meta?.currentPage ?? 1);
        void loadSection('special', sections.special.meta?.currentPage ?? 1);
    };

    onMounted(() => {
        void loadSection('team');
        void loadSection('special');
    });

    return {
        sections,
        loadSection,
        setQuestPublished,
        reloadTeamSections,
    };
}
