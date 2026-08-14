import axios from 'axios';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';

export interface SaveMentorToolPayload {
    name: string;
}

export const fetchMentorTools = async (): Promise<MentorTool[]> => {
    const { data } = await axios.get<{ data: MentorTool[] }>('/api/mentor/tools');

    return data.data;
};

export const createMentorTool = async (payload: SaveMentorToolPayload): Promise<MentorTool> => {
    const { data } = await axios.post<{ data: MentorTool }>('/api/mentor/tools', payload);

    return data.data;
};

export const updateMentorTool = async (
    toolId: number,
    payload: SaveMentorToolPayload,
): Promise<MentorTool> => {
    const { data } = await axios.put<{ data: MentorTool }>(`/api/mentor/tools/${toolId}`, payload);

    return data.data;
};
