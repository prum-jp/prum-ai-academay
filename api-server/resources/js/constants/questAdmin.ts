import type { MentorQuestCreateType } from '@/types/questAdmin';
import type { QuestType } from '@/types/quest';
import { questSectionDefinitions } from '@/constants/quests';
import { statDefinitions } from '@/constants/stats';

export const mentorQuestPageConfig = {
    title: 'クエスト管理',
    icon: 'fa-solid fa-scroll',
} as const;

export const mentorQuestBoardCardConfig = {
    title: 'クエストカタログ',
    icon: 'fa-solid fa-map',
    description:
        '個人ユニット・チーム・特別クエストを確認できます。新規追加からカタログに登録できます。',
    createButtonLabel: '新規追加',
} as const;

export const mentorQuestCreateModalConfig = {
    title: '新規登録',
    icon: 'fa-solid fa-plus',
    typeLabel: '種別',
    submitLabel: '登録する',
    submittingLabel: '登録中...',
    cancelLabel: 'キャンセル',
} as const;

export const mentorQuestCreateTypeOptions: Array<{
    value: MentorQuestCreateType;
    label: string;
}> = [
    { value: 'personal', label: '個人（ユニット）' },
    { value: 'team', label: 'チーム' },
    { value: 'special', label: '特別' },
];

export type NonPersonalQuestType = Exclude<QuestType, 'personal'>;

export const mentorQuestAdminSectionDefinitions = questSectionDefinitions;

export const mentorQuestAdminMessages = {
    loadUnitsFailed: 'ユニット一覧の取得に失敗しました。',
    loadQuestsFailed: 'クエスト一覧の取得に失敗しました。',
    loadUnitDetailFailed: 'ユニット詳細の取得に失敗しました。',
    createUnitFailed: 'ユニットの登録に失敗しました。',
    createQuestFailed: 'クエストの登録に失敗しました。',
    updateUnitFailed: 'ユニットの更新に失敗しました。',
    updateQuestFailed: 'クエストの更新に失敗しました。',
    deleteUnitFailed: 'ユニットの削除に失敗しました。',
    deleteQuestFailed: 'クエストの削除に失敗しました。',
    createUnitSuccessToast: 'ユニットを登録しました！',
    createQuestSuccessToast: 'クエストを登録しました！',
    updateUnitSuccessToast: 'ユニットを更新しました！',
    updateQuestSuccessToast: 'クエストを更新しました！',
    deleteUnitSuccessToast: 'ユニットを削除しました。',
    deleteQuestSuccessToast: 'クエストを削除しました。',
    deleteUnitConfirm: 'このユニットと紐づく子クエストを削除します。よろしいですか？',
    deleteQuestConfirm: 'このクエストを削除します。よろしいですか？',
    loading: '読み込み中...',
    emptyUnits: 'ユニットはまだありません。',
    emptyQuests: 'クエストはまだありません。',
    unitQuestCountLabel: (count: number): string => `${count}クエスト`,
} as const;

export const mentorQuestEditModalConfig = {
    unitTitle: 'ユニットを編集',
    questTitle: 'クエストを編集',
    icon: 'fa-solid fa-pen',
    submitLabel: '更新する',
    submittingLabel: '更新中...',
    cancelLabel: 'キャンセル',
} as const;

export const mentorQuestAdminCardActions = {
    editLabel: '編集',
    deleteLabel: '削除',
} as const;

export const mentorQuestPublishMessages = {
    publishedToast: '公開しました！学生に表示されます。',
    unpublishedToast: '非公開にしました。学生には表示されません。',
    publishFailed: '公開状態の変更に失敗しました。',
} as const;

export const mentorChildQuestFormLabels = {
    sectionTitle: '子クエスト',
    title: 'クエストタイトル',
    description: '概要',
    clearCondition: '完了条件',
    tool: '使用ツール',
    toolNone: '（なし）',
    add: 'クエストを追加',
    remove: '削除',
    empty: '子クエストはまだありません。「クエストを追加」から登録できます。',
} as const;

export const mentorQuestUnitFormLabels = {
    title: 'ユニットタイトル',
    description: '概要',
    rewardText: '報酬テキスト（学生向け表示）',
    rewardsTitle: '成長ステータス報酬',
    addReward: '報酬を追加',
    removeReward: '削除',
    stat: '成長ステータス',
    points: 'ポイント',
} as const;

export const mentorQuestFormLabels = {
    title: 'クエストタイトル',
    description: '概要',
    clearCondition: '完了条件',
    rewardText: '報酬テキスト（学生向け表示）',
    badgeLabel: 'バッジラベル',
    unlockLevel: '解放レベル',
    isRequired: '必須クエストにする',
    rewardsTitle: '成長ステータス報酬',
    addReward: '報酬を追加',
    removeReward: '削除',
    stat: '成長ステータス',
    points: 'ポイント',
} as const;

export const mentorQuestFormPlaceholders = {
    unitTitle: '例：千本ノック基礎編',
    questTitle: '例：チーム：業務効率化ツールを共同作成する',
    description: 'クエストの概要を入力',
    clearCondition: '完了条件を入力',
    rewardText: '例：AI親和性 +1、課題発見力 +1',
    badgeLabel: '例：Lv.3以上で開放',
    unlockLevel: '未設定の場合は空欄',
} as const;

export const mentorQuestStatOptions = statDefinitions.map((item) => ({
    value: item.key,
    label: item.label,
}));

export const createEmptyReward = () => ({
    stat: mentorQuestStatOptions[0]?.value ?? 'aiAffinity',
    points: 1,
});
