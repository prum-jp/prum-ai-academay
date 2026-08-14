import type { QuestMasterKindFilter } from '@/types/mentor-master/questMaster';
import { questImportKindLabels } from '@/constants/mentor-quest/questImport';

export const mentorQuestMasterPageConfig = {
    title: '管理 / クエストマスタ',
    icon: 'fa-solid fa-book',
    description:
        '登録済みのクエストをユニット単位で確認できます。内容の更新は CSV 一括登録、新規追加は UI または CSV から行えます。',
    searchLabel: '検索',
    searchPlaceholder: 'Unit名・Quest名で検索',
    kindFilterLabel: '種別',
    kindAllLabel: 'すべて',
    exportCsvLabel: 'CSVダウンロード',
    exportCsvHint: '個人ユニット（子クエスト）のみ出力されます。',
    csvUpdateLabel: 'CSVで更新',
    createLabel: '新規追加',
    operationsLinkLabel: 'クエスト管理へ',
    loadingLabel: '読み込み中...',
    emptyLabel: '該当するクエストはありません。',
    unitSortLabel: (sortOrder: number): string => `Unit ${sortOrder}`,
    unitQuestCountLabel: (count: number): string => `${count}件`,
    emptyUnitQuests: 'このユニットにはクエストがまだありません。',
} as const;

export const mentorQuestMasterMenuConfig = {
    menuLabel: 'メニューを開く',
    detailLabel: '詳細',
    editLabel: '編集',
    deleteLabel: '削除',
} as const;

export const mentorQuestMasterDeleteModalConfig = {
    title: '削除の確認',
    icon: 'fa-solid fa-trash',
    cancelLabel: 'キャンセル',
    confirmLabel: '削除する',
    deletingLabel: '削除中...',
    loadingLabel: '確認中...',
    loadFailed: '削除前の確認情報を取得できませんでした。',
    deleteFailed: '削除に失敗しました。',
    deleteSuccess: '削除しました。',
} as const;

export const mentorQuestMasterDeleteMessages = {
    unitConfirm: (title: string): string =>
        `「${title}」と、このユニットに紐づく子クエストを削除します。よろしいですか？`,
    questConfirm: (title: string): string => `「${title}」を削除します。よろしいですか？`,
    linkedUsersWarning: (count: number): string =>
        `${count}人の受講生に紐づいています。進捗・割当・提出データも削除されます。`,
    submissionsWarning: '提出済みのデータがあります。削除すると復元できません。',
    unitChildQuestCount: (count: number): string => `子クエスト ${count}件も削除されます。`,
} as const;

export const mentorQuestMasterDetailPageConfig = {
    backLabel: 'クエストマスタに戻る',
    editLabel: '編集する',
    questsSectionTitle: '含まれるクエスト',
    loadingLabel: '読み込み中...',
    loadFailed: '詳細の取得に失敗しました。',
} as const;

export const mentorQuestMasterEditPageConfig = {
    backLabel: 'クエストマスタに戻る',
    detailLabel: '詳細を見る',
    submitLabel: '更新する',
    submittingLabel: '更新中...',
    loadFailed: '編集データの取得に失敗しました。',
    updateFailed: '更新に失敗しました。',
    updateSuccess: '更新しました。',
    rewardsSectionTitle: '付与スキル',
} as const;

export const mentorQuestMasterMessages = {
    loadFailed: 'クエストマスタの取得に失敗しました。',
    exportFailed: 'CSVのダウンロードに失敗しました。',
    exportSuccess: 'CSVをダウンロードしました。',
    imported: 'CSV一括登録を反映しました。',
} as const;

export const mentorQuestMasterKindOptions: Array<{
    value: QuestMasterKindFilter;
    label: string;
}> = [
    { value: 'all', label: mentorQuestMasterPageConfig.kindAllLabel },
    { value: 'personal_unit', label: questImportKindLabels.personal_unit },
    { value: 'child_quest', label: questImportKindLabels.child_quest },
    { value: 'team_quest', label: questImportKindLabels.team_quest },
    { value: 'special_quest', label: questImportKindLabels.special_quest },
];

export const mentorQuestMasterSectionLabels = {
    team: questImportKindLabels.team_quest,
    special: questImportKindLabels.special_quest,
} as const;
