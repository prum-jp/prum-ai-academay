import axios from 'axios';
import type {
    CreateMentorQuestPayload,
    CreateMentorQuestUnitPayload,
    MentorQuestDetail,
    MentorQuestItem,
    MentorQuestListResponse,
    MentorQuestUnitDetail,
    MentorQuestUnitItem,
    MentorQuestUnitListResponse,
    UpdateMentorPersonalQuestPayload,
    UpdateMentorQuestPayload,
    UpdateMentorQuestUnitPayload,
} from '@/types/mentor-quest/questAdmin';
import type { QuestType } from '@/types/quest/quest';

export const fetchMentorQuestUnits = async (
    page = 1,
): Promise<MentorQuestUnitListResponse> => {
    const { data } = await axios.get<MentorQuestUnitListResponse>('/api/mentor/quest-units', {
        params: { page },
    });

    return data;
};

export const fetchMentorQuests = async (
    type: Extract<QuestType, 'team' | 'special'>,
    page = 1,
): Promise<MentorQuestListResponse> => {
    const { data } = await axios.get<MentorQuestListResponse>('/api/mentor/quests', {
        params: { type, page },
    });

    return data;
};

export const createMentorQuestUnit = async (
    payload: CreateMentorQuestUnitPayload,
): Promise<MentorQuestUnitItem> => {
    const { data } = await axios.post<{ data: MentorQuestUnitItem }>(
        '/api/mentor/quest-units',
        payload,
    );

    return data.data;
};

export const reorderMentorQuestUnits = async (unitIds: number[]): Promise<void> => {
    await axios.put('/api/mentor/quest-units/reorder', { unitIds });
};

export const createMentorQuest = async (
    payload: CreateMentorQuestPayload,
): Promise<MentorQuestItem> => {
    const { data } = await axios.post<{ data: MentorQuestItem }>('/api/mentor/quests', payload);

    return data.data;
};

export { fetchMentorTools } from '@/api/mentor-tools/toolAdmin';

export const fetchMentorQuestUnitDetail = async (
    id: number,
): Promise<MentorQuestUnitDetail> => {
    const { data } = await axios.get<{ data: MentorQuestUnitDetail }>(
        `/api/mentor/quest-units/${id}`,
    );

    return data.data;
};

export const updateMentorQuestUnit = async (
    id: number,
    payload: UpdateMentorQuestUnitPayload,
): Promise<MentorQuestUnitItem> => {
    const { data } = await axios.put<{ data: MentorQuestUnitItem }>(
        `/api/mentor/quest-units/${id}`,
        payload,
    );

    return data.data;
};

export const deleteMentorQuestUnit = async (id: number): Promise<void> => {
    await axios.delete(`/api/mentor/quest-units/${id}`);
};

export const fetchMentorQuestDetail = async (id: number): Promise<MentorQuestDetail> => {
    const { data } = await axios.get<{ data: MentorQuestDetail }>(`/api/mentor/quests/${id}`);

    return data.data;
};

export const updateMentorPersonalQuest = async (
    id: number,
    payload: UpdateMentorPersonalQuestPayload,
): Promise<MentorQuestDetail> => {
    const { data } = await axios.put<{ data: MentorQuestDetail }>(
        `/api/mentor/quests/${id}/personal`,
        payload,
    );

    return data.data;
};

export const updateMentorQuest = async (
    id: number,
    payload: UpdateMentorQuestPayload,
): Promise<MentorQuestItem> => {
    const { data } = await axios.put<{ data: MentorQuestItem }>(
        `/api/mentor/quests/${id}`,
        payload,
    );

    return data.data;
};

export const deleteMentorQuest = async (id: number): Promise<void> => {
    await axios.delete(`/api/mentor/quests/${id}`);
};

export const publishMentorQuest = async (
    id: number,
    isPublished: boolean,
): Promise<MentorQuestItem> => {
    const { data } = await axios.patch<{ data: MentorQuestItem }>(
        `/api/mentor/quests/${id}/publish`,
        { isPublished },
    );

    return data.data;
};
