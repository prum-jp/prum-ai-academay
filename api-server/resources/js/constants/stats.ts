import type { AdventurerStats } from '@/types/adventurer';

export interface StatDefinition {
    key: keyof AdventurerStats;
    label: string;
    icon: string;
}

export const statDefinitions: StatDefinition[] = [
    { key: 'presentation', label: 'プレゼン力', icon: 'fa-solid fa-bullhorn' },
    { key: 'communication', label: 'コミュニケーション', icon: 'fa-solid fa-comments' },
    { key: 'problemFinding', label: '課題発見力', icon: 'fa-solid fa-magnifying-glass' },
    { key: 'aiAffinity', label: 'AI親和性', icon: 'fa-solid fa-robot' },
    { key: 'action', label: 'まず動く行動力', icon: 'fa-solid fa-shoe-prints' },
    { key: 'support', label: 'サポート精神', icon: 'fa-solid fa-handshake-angle' },
];
