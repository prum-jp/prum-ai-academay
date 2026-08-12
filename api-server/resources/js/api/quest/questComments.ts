import axios from 'axios';
import type { QuestComment, QuestCommentListResponse } from '@/types/quest/questComment';

export const fetchQuestComments = async (questId: number): Promise<QuestComment[]> => {
    const { data } = await axios.get<QuestCommentListResponse>(`/api/quests/${questId}/comments`);

    return data.data;
};

export const postQuestComment = async (questId: number, body: string): Promise<QuestComment> => {
    const { data } = await axios.post<QuestComment>(`/api/quests/${questId}/comments`, {
        body,
    });

    return data;
};
