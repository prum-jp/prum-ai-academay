import type { QuestListMeta } from '@/types/quest/quest';

export interface QuestMasterQuestRow {
    id: number;
    kind: 'child_quest' | 'team_quest' | 'special_quest';
    title: string;
    sortOrder: number;
    unitId?: number | null;
}

export interface QuestMasterUnitGroup {
    id: number;
    title: string;
    sortOrder: number;
    questCount: number;
    quests: QuestMasterQuestRow[];
}

export interface QuestMasterGroupedData {
    units: QuestMasterUnitGroup[];
    teamQuests: QuestMasterQuestRow[];
    specialQuests: QuestMasterQuestRow[];
}

export interface QuestMasterListResponse {
    data: QuestMasterGroupedData;
    meta: QuestListMeta;
}

export type QuestMasterKindFilter =
    | 'all'
    | 'personal_unit'
    | 'child_quest'
    | 'team_quest'
    | 'special_quest';
