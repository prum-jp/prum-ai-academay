import type { SkillKey } from '@/constants/shared/skills';
import type { QuestTier } from '@/constants/quest/questTier';

export type QuestImportKind = 'personal_unit' | 'child_quest' | 'team_quest' | 'special_quest';
export type QuestImportAction = 'create' | 'update' | 'unchanged';

/** API 送受信・プレビュー編集用（子クエストは概要/目的/提出物/完了条件を列単位で保持） */
export interface QuestImportItem {
    clientId: string;
    kind: QuestImportKind;
    id?: number | null;
    unitTitle?: string;
    title: string;
    /** CSV 概要列（子クエスト） */
    overview?: string;
    /** CSV 目的列（子クエスト） */
    purpose?: string;
    /** CSV 提出物列（子クエスト） */
    deliverable?: string;
    /** CSV 完了条件列（子クエスト） */
    completionCondition?: string;
    description?: string;
    rewardText?: string;
    clearCondition?: string;
    estimatedDuration?: string;
    toolCode?: string;
    skillGrants: SkillKey[];
    sortOrder?: number;
    isRequired?: boolean;
    unlockLevel?: number | null;
    /** 低 / 中 / 高 / エキスパート（子クエスト用） */
    questTier?: QuestTier;
    badgeLabel?: string;
    /** CSV No（通し番号・表示用） */
    csvNo?: string;
    /** CSV Unit 列（ユニット並び順） */
    unitSortOrder?: number;
    /** CSV To do 列（取り込み対象外・プレビュー表示のみ） */
    todoNote?: string;
    /** CSV 難度（1〜5。quests.difficulty に保存。XPは難度×4で自動計算） */
    difficulty?: number;
    action?: QuestImportAction;
    existingId?: number | null;
    errors?: string[];
}

export interface QuestImportMeta {
    total: number;
    createCount: number;
    updateCount: number;
    unchangedCount: number;
    errorCount: number;
}

export interface QuestImportPreviewResponse {
    data: QuestImportItem[];
    meta: QuestImportMeta;
}

export interface QuestImportApplyResult {
    kind: QuestImportKind;
    action: string;
    id?: number | null;
    title?: string;
    unitId?: number;
    unitTitle?: string;
}

export interface QuestImportApplyResponse {
    data: QuestImportApplyResult[];
    meta: {
        appliedCount: number;
    };
}

export type QuestImportPayloadItem = Omit<
    QuestImportItem,
    | 'clientId'
    | 'action'
    | 'existingId'
    | 'errors'
    | 'csvNo'
    | 'unitSortOrder'
    | 'todoNote'
    | 'overview'
    | 'purpose'
    | 'deliverable'
    | 'completionCondition'
>;
