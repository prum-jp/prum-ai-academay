import axios from 'axios';
import type {
    QuestItem,
    QuestListResponse,
    QuestType,
    QuestUnitListResponse,
} from '@/types/quest/quest';
import type { UnitProgressFilter } from '@/constants/quest/unitProgress';
import { DEFAULT_UNIT_PROGRESS_FILTER } from '@/constants/quest/unitProgress';
import type { QuestSubmissionPayload } from '@/types/quest/questSubmission';
import { patchQuestProgress } from '@/api/quest/questProgress';
import type { QuestProgressStatus } from '@/constants/quest/questProgress';

export const fetchQuests = async (
    type: QuestType,
    page = 1,
): Promise<QuestListResponse> => {
    const { data } = await axios.get<QuestListResponse>('/api/quests', {
        params: { type, page },
    });

    return data;
};

export const fetchQuestUnits = async (
    page = 1,
    progressFilter: UnitProgressFilter = DEFAULT_UNIT_PROGRESS_FILTER,
): Promise<QuestUnitListResponse> => {
    const { data } = await axios.get<QuestUnitListResponse>('/api/quest-units', {
        params: { page, progressFilter },
    });

    return data;
};

export const fetchQuest = async (questId: number): Promise<QuestItem> => {
    const { data } = await axios.get<{ data: QuestItem; studentLevel: number }>(
        `/api/quests/${questId}`,
    );

    return data.data;
};

export const updateQuestProgress = async (
    questId: number,
    status: QuestProgressStatus,
): Promise<QuestItem> => patchQuestProgress(questId, status, 'student');

export const submitQuestSubmission = async (
    questId: number,
    payload: QuestSubmissionPayload,
): Promise<QuestItem> => {
    if (payload.type === 'image' && payload.file) {
        return addQuestSubmissionImage(questId, payload.file);
    }

    if (payload.file) {
        const formData = new FormData();
        formData.append('type', payload.type);
        formData.append('file', payload.file);

        const { data } = await axios.post<QuestItem>(`/api/quests/${questId}/submission`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        return data;
    }

    const { data } = await axios.patch<QuestItem>(`/api/quests/${questId}/submission`, {
        type: payload.type,
        url: payload.url,
        text: payload.text,
    });

    return data;
};

export const addQuestSubmissionImage = async (
    questId: number,
    file: File,
): Promise<QuestItem> => {
    const formData = new FormData();
    formData.append('file', file);

    const { data } = await axios.post<QuestItem>(
        `/api/quests/${questId}/submission/images`,
        formData,
        {
            headers: { 'Content-Type': 'multipart/form-data' },
        },
    );

    return data;
};

export const deleteQuestSubmissionImage = async (
    questId: number,
    fileId: number,
): Promise<QuestItem> => {
    const { data } = await axios.delete<QuestItem>(
        `/api/quests/${questId}/submission/images/${fileId}`,
    );

    return data;
};
