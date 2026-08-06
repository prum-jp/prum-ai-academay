import axios from 'axios';
import type { QuestMasterKindFilter, QuestMasterListResponse } from '@/types/questMaster';

export const fetchMentorQuestMaster = async (params: {
    kind?: QuestMasterKindFilter;
    search?: string;
    page?: number;
}): Promise<QuestMasterListResponse> => {
    const { data } = await axios.get<QuestMasterListResponse>('/api/mentor/quests/master', {
        params: {
            page: params.page ?? 1,
            search: params.search?.trim() || undefined,
            kind: params.kind && params.kind !== 'all' ? params.kind : undefined,
        },
    });

    return data;
};

export const downloadMentorQuestMasterCsv = async (): Promise<void> => {
    const response = await axios.get<Blob>('/api/mentor/quests/master/export', {
        responseType: 'blob',
    });

    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    link.download = 'quest-master.csv';
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
};
