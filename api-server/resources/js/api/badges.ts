import axios from 'axios';
import type { BadgeListResponse } from '@/types/badge';

export const fetchBadges = async (): Promise<BadgeListResponse> => {
    const { data } = await axios.get<BadgeListResponse>('/api/badges');

    return data;
};
