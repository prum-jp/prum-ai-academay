import axios from 'axios';
import type { QuestTier } from '@/constants/quest/questTier';
import type {
    QuestImportApplyResponse,
    QuestImportPreviewResponse,
    QuestImportPayloadItem,
} from '@/types/mentor-quest/questImport';

export const previewQuestImport = async (
    items: QuestImportPayloadItem[],
    defaultQuestTier?: QuestTier,
): Promise<QuestImportPreviewResponse> => {
    const { data } = await axios.post<QuestImportPreviewResponse>(
        '/api/mentor/quests/import/preview',
        { items, defaultQuestTier },
    );

    return data;
};

export const applyQuestImport = async (
    items: QuestImportPayloadItem[],
    defaultQuestTier?: QuestTier,
): Promise<QuestImportApplyResponse> => {
    const { data } = await axios.post<QuestImportApplyResponse>(
        '/api/mentor/quests/import/apply',
        { items, defaultQuestTier },
    );

    return data;
};
