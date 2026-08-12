// TODO: 後に機能追加 — 実績バッジ API（ルートもコメントアウト中）
import axios from 'axios';
import type { BadgeListResponse } from '@/types/badge/badge';

export const fetchBadges = async (): Promise<BadgeListResponse> => {
    const { data } = await axios.get<BadgeListResponse>('/api/badges');

    return data;
};
