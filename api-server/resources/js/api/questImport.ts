import axios from 'axios';
import type {
    QuestImportApplyResponse,
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
): Promise<QuestImportApplyResponse> => {
    const { data } = await axios.post<QuestImportApplyResponse>(
        '/api/mentor/quests/import/apply',
        { items },
    );

    return data;
};
