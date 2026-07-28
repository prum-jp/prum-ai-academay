export interface BadgeItem {
    id: number;
    code: string;
    title: string;
    description: string;
    icon: string;
    isEarned: boolean;
}

export interface BadgeListMeta {
    earnedCount: number;
    totalCount: number;
}

export interface BadgeListResponse {
    data: BadgeItem[];
    meta: BadgeListMeta;
}
