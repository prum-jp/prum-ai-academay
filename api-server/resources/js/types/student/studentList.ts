export interface StudentListItem {
    id: number;
    name: string;
    avatarUrl: string | null;
    levelTitle: string;
    earnedBadgeCount: number;
    email?: string;
    isSelected?: boolean;
}
