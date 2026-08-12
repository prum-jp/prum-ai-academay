import type { MentorQuestCreateType } from '@/types/mentor-quest/questAdmin';
import type { QuestType } from '@/types/quest/quest';
import { questSectionDefinitions } from '@/constants/quest/quests';
import { createEmptySkillGrants as emptySkillGrants } from '@/constants/shared/skills';

export const mentorQuestPageConfig = {
    title: '管理 / クエスト管理',
    icon: 'fa-solid fa-scroll',
} as const;

export const mentorPersonalAssignmentSectionConfig = {
    title: '個人クエスト',
    icon: 'fa-solid fa-seedling',
    description:
        '受講生ごとに個人ユニットを割り当てます。行末の ⋮ からクエスト割当やクエスト一覧を開けます。',
    menuLabel: 'メニューを開く',
    menuAssignLabel: 'クエスト割り当て',
    menuHomeLabel: '受講者シート',
    modalTitle: 'クエスト割当',
    modalDescription:
        'ユニットをクリックで含まれるクエストを表示できます。バッジをクリックで割当・解除できます。',
    assignedStatusLabel: '割当済み',
    unassignedStatusLabel: '未割当',
    viaCurriculumStatusLabel: 'カリキュラム経由',
    unitTypeLabel: 'ユニット',
    childQuestTypeLabel: 'クエスト',
    emptyChildQuests: '含まれるクエストはまだありません。',
    assignedSummaryLabel: (assigned: number, total: number): string =>
        `${assigned} / ${total} 件割当済み`,
    loadingQuests: 'クエスト一覧を読み込んでいます...',
    emptyQuests: '登録されている個人ユニットはまだありません。',
    assignSuccess: '割り当てました！',
    unassignSuccess: '割当を解除しました。',
    assignFailed: '割当に失敗しました。',
    unassignFailed: '割当解除に失敗しました。',
    viaCurriculumNote: 'カリキュラム経由',
    dragHandleLabel: 'ユニットの並び替え',
    reorderFailed: 'ユニットの並び順更新に失敗しました。',
} as const;

export const mentorQuestBoardCardConfig = {
    title: 'クエスト管理',
    icon: 'fa-solid fa-map',
    description:
        '個人ユニットは受講生ごとに割り当て、チーム・特別クエストは公開・削除を管理できます。内容の確認・更新はクエストマスタから行えます。',
    masterLinkLabel: 'クエストマスタ',
    userRegisterLinkLabel: 'ユーザー追加',
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

export const mentorQuestCreatePageConfig = {
    title: '管理 / クエスト新規登録',
    icon: 'fa-solid fa-plus',
    backLabel: 'クエスト管理に戻る',
    typeLabel: '種別',
    submitLabel: '登録する',
    submittingLabel: '登録中...',
    cancelLabel: 'キャンセル',
    rewardsSectionTitle: '付与スキル',
    addChildQuestLabel: 'クエストを追加する',
    addedChildQuestsTitle: '追加済みクエスト',
    childQuestSectionTitle: 'クエスト内容',
} as const;

export const mentorQuestUnitAssignModalConfig = {
    title: '反映する受講生',
    icon: 'fa-solid fa-users',
    description: '登録したユニットを、どの受講生に割り当てるか選んでください。',
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
    difficultyPlaceholder: '1〜5',
    questTier: 'クエスト段階',
    add: 'クエストを追加',
    remove: '削除',
    empty: '子クエストはまだありません。「クエストを追加」から登録できます。',
} as const;

export const mentorQuestUnitFormLabels = {
    title: 'ユニットタイトル',
    rewardsTitle: '付与スキル',
    addReward: '報酬を追加',
    removeReward: '削除',
    stat: 'スキル',
    points: 'ポイント',
} as const;

export const mentorQuestFormLabels = {
    title: 'クエストタイトル',
    description: '概要',
    clearCondition: '完了条件',
    rewardText: '報酬テキスト（学生向け表示）',
    badgeLabel: 'バッジラベル',
    unlockLevel: '解放レベル',
    experiencePoints: '獲得XP',
    isRequired: '必須クエストにする',
    rewardsTitle: '付与スキル',
    addReward: '報酬を追加',
    removeReward: '削除',
    stat: 'スキル',
    points: 'ポイント',
} as const;

export const mentorQuestFormPlaceholders = {
    unitTitle: '例：AI基礎編',
    questTitle: '例：チーム：業務効率化ツールを共同作成する',
    childQuestTitle: '例：Day1 プロンプト基礎',
    description: 'クエストの概要を入力',
    clearCondition: '完了条件を入力',
    rewardText: '例：AIリテラシー、課題発見力',
    badgeLabel: '例：Lv.3以上で開放',
    unlockLevel: '未設定の場合は空欄',
    experiencePoints: '例: 10',
} as const;

export { emptySkillGrants as createEmptySkillGrants };
