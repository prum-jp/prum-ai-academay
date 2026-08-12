import axios from 'axios';
import type { AuthUser } from '@/types/shared/auth';

export const normalizeAuthUser = (raw: AuthUser): AuthUser => ({
    ...raw,
    role: Number(raw.role),
});

export const loginRequest = async (email: string, password: string): Promise<AuthUser> => {
    const { data } = await axios.post<{ user: AuthUser }>('/api/login', {
        email,
        password,
    });

    return normalizeAuthUser(data.user);
};

export const logoutRequest = async (): Promise<void> => {
    await axios.post('/api/logout');
};

export const fetchMeRequest = async (): Promise<AuthUser> => {
    const { data } = await axios.get<{ user: AuthUser }>('/api/me');

    return normalizeAuthUser(data.user);
};
