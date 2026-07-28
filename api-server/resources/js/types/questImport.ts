export type QuestImportKind = 'personal_unit' | 'child_quest' | 'team_quest' | 'special_quest';
export type QuestImportAction = 'create' | 'update' | 'unchanged';

export interface QuestImportReward {
    stat: string;
    points: number;
}

/** API 送受信・プレビュー編集用（CSV 列は parseQuestImportCsv.ts で変換） */
export interface QuestImportItem {
    clientId: string;
    kind: QuestImportKind;
    id?: number | null;
    unitTitle?: string;
    title: string;
    description?: string;
    rewardText?: string;
    clearCondition?: string;
    estimatedDuration?: string;
    toolCode?: string;
    rewards: QuestImportReward[];
    isPublished: boolean;
    sortOrder?: number;
    isRequired?: boolean;
    unlockLevel?: number | null;
    badgeLabel?: string;
    /** CSV No（通し番号・表示用） */
    csvNo?: string;
    /** CSV Unit 列（ユニット並び順） */
    unitSortOrder?: number;
    /** CSV To do 列（取り込み対象外・プレビュー表示のみ） */
    todoNote?: string;
    /** CSV 難度列（DB未対応・プレビュー表示のみ） */
    difficulty?: string;
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

export type QuestImportPayloadItem = Omit<
    QuestImportItem,
    | 'clientId'
    | 'action'
    | 'existingId'
    | 'errors'
    | 'csvNo'
    | 'unitSortOrder'
    | 'todoNote'
    | 'difficulty'
>;
