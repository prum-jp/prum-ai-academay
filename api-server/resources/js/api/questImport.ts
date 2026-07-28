import axios from 'axios';
import type {
    QuestImportPreviewResponse,
    QuestImportPayloadItem,
} from '@/types/questImport';

export const previewQuestImport = async (
    items: QuestImportPayloadItem[],
): Promise<QuestImportPreviewResponse> => {
    const { data } = await axios.post<QuestImportPreviewResponse>(
        '/api/mentor/quests/import/preview',
        { items },
    );

    return data;
};

export const applyQuestImport = async (
    items: QuestImportPayloadItem[],
): Promise<{ data: unknown[]; meta: { appliedCount: number } }> => {
    const { data } = await axios.post<{ data: unknown[]; meta: { appliedCount: number } }>(
        '/api/mentor/quests/import/apply',
        { items },
    );

    return data;
};
