import axios from 'axios';
import type { AdventurerProfile, AdventurerStats } from '@/types/adventurer';

export interface StudentProfileUpdatePayload {
    name: string;
    background: string;
    hobby: string;
    weaponSkill: string;
    spellGoal: string;
}

export interface StudentStatUpdatePayload {
    stat: keyof AdventurerStats;
    delta: 1 | -1;
}

export const fetchStudentProfile = async (): Promise<AdventurerProfile> => {
    const { data } = await axios.get<AdventurerProfile>('/api/profile');

    return data;
};

export const updateStudentProfile = async (
    payload: StudentProfileUpdatePayload,
): Promise<AdventurerProfile> => {
    const { data } = await axios.patch<AdventurerProfile>('/api/profile', payload);

    return data;
};

export const updateStudentStat = async (
    payload: StudentStatUpdatePayload,
): Promise<AdventurerProfile> => {
    const { data } = await axios.patch<AdventurerProfile>('/api/profile/stats', payload);

    return data;
};

export const uploadStudentAvatar = async (file: File): Promise<AdventurerProfile> => {
    const formData = new FormData();
    formData.append('avatar', file);

    const { data } = await axios.post<AdventurerProfile>('/api/profile/avatar', formData);

    return data;
};

export const deleteStudentAvatar = async (): Promise<AdventurerProfile> => {
    const { data } = await axios.delete<AdventurerProfile>('/api/profile/avatar');

    return data;
};
