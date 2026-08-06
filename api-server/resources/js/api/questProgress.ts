import axios from 'axios';
import type { QuestProgressStatus } from '@/constants/questProgress';
import type { QuestItem } from '@/types/quest';

export type QuestProgressRole = 'student' | 'mentor';

const progressEndpoint = (questId: number, role: QuestProgressRole): string =>
    role === 'mentor'
        ? `/api/mentor/quests/${questId}/progress`
        : `/api/quests/${questId}/progress`;

export const patchQuestProgress = async (
    questId: number,
    status: QuestProgressStatus,
    role: QuestProgressRole = 'student',
): Promise<QuestItem> => {
    const { data } = await axios.patch<QuestItem>(progressEndpoint(questId, role), {
        status,
    });

    return data;
};
