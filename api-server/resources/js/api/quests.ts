import axios from 'axios';
import type {
    QuestItem,
    QuestListResponse,
    QuestType,
    QuestUnitListResponse,
} from '@/types/quest';

export const fetchQuests = async (
    type: QuestType,
    page = 1,
): Promise<QuestListResponse> => {
    const { data } = await axios.get<QuestListResponse>('/api/quests', {
        params: { type, page },
    });

    return data;
};

export const fetchQuestUnits = async (page = 1): Promise<QuestUnitListResponse> => {
    const { data } = await axios.get<QuestUnitListResponse>('/api/quest-units', {
        params: { page },
    });

    return data;
};

export const toggleQuestProgress = async (questId: number): Promise<QuestItem> => {
    const { data } = await axios.patch<QuestItem>(`/api/quests/${questId}/progress`);

    return data;
};
