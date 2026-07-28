import axios from 'axios';
import type { MentorTool } from '@/types/questAdmin';

export interface CreateMentorToolPayload {
    code: string;
    name: string;
}

export const fetchMentorTools = async (): Promise<MentorTool[]> => {
    const { data } = await axios.get<{ data: MentorTool[] }>('/api/mentor/tools');

    return data.data;
};

export const createMentorTool = async (payload: CreateMentorToolPayload): Promise<MentorTool> => {
    const { data } = await axios.post<{ data: MentorTool }>('/api/mentor/tools', payload);

    return data.data;
};
