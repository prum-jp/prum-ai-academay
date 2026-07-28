/**
 * CSV 列定義（quests テーブル対応）。
 *
 * | CSV列     | DB / アプリ           |
 * |----------|----------------------|
 * | No        | クエスト sort_order（優先） |
 * | Unit      | ユニット sort_order   |
 * | Quest     | クエスト sort_order（No が空のとき） |
 * | Unit概要   | ユニット description（任意） |
 * | 内容      | クエスト description / ユニット概要のフォールバック |
 * | 目的      | description 内【目的】 |
 * | 完了条件   | clear_condition      |
 * | 所要時間   | estimated_duration   |
 *
 * 内容に【提出物】がある場合、完了条件列が空ならそこから自動分割する。
 * 難度は DB 未対応（プレビューのみ）。
 */
export const questImportCsvColumns = [
    'No',
    'Unit',
    'Quest',
    'To do',
    'Unit名',
    'Quest名',
    '内容',
    '目的',
    '完了条件',
    'ツール',
    '難度',
    '所要時間',
] as const;

export const questImportActionLabels = {
    create: '新規',
    update: '更新',
    unchanged: '変化なし',
} as const;

export const questImportPublishLabels = {
    on: '公開',
    off: '非公開',
} as const;

export const questImportKindLabels: Record<string, string> = {
    personal_unit: '個人ユニット',
    child_quest: '子クエスト',
    team_quest: 'チーム',
    special_quest: '特別',
};

export const questImportModalConfig = {
    title: 'CSV一括登録',
    icon: 'fa-solid fa-file-csv',
    bulkButtonLabel: '一括登録',
    uploadLabel: 'CSVファイル',
    uploadHint:
        'UTF-8 の CSV を選択してください。並び順はクエストが No、ユニットが Unit（なければ最小 No）に対応します。',
    cancelLabel: 'キャンセル',
    backLabel: 'ファイル選択に戻る',
    applyLabel: '反映する',
    applyingLabel: '反映中...',
    previewLoadingLabel: 'プレビューを生成しています...',
    publishAllOnLabel: 'すべて公開',
    publishAllOffLabel: 'すべて非公開',
} as const;

export const questImportMessages = {
    previewFailed: 'プレビューの生成に失敗しました。',
    applyFailed: '一括登録の反映に失敗しました。',
    applySuccess: 'CSV一括登録を反映しました！',
    emptyFile: '取り込める行がありません。',
    hasErrors: 'エラーがある行があります。修正または削除してから反映してください。',
    summary: (meta: {
        createCount: number;
        updateCount: number;
        unchangedCount?: number;
        errorCount: number;
    }): string =>
        `新規 ${meta.createCount} 件 / 更新 ${meta.updateCount} 件` +
        (meta.unchangedCount && meta.unchangedCount > 0
            ? ` / 変化なし ${meta.unchangedCount} 件`
            : '') +
        (meta.errorCount > 0 ? ` / エラー ${meta.errorCount} 件` : ''),
} as const;

export const questImportFieldLabels = {
    csvNo: 'No',
    unitSortOrder: 'Unit',
    sortOrder: 'Quest',
    todoNote: 'To do',
    unitTitle: 'Unit名',
    title: 'Quest名',
    description: '概要（内容）',
    clearCondition: '完了条件',
    toolCode: 'ツール',
    estimatedDuration: '所要時間',
    difficulty: '難度',
    isPublished: '公開',
    childQuests: '含まれるクエスト',
    expandLabel: '編集/詳細',
    collapseLabel: '閉じる',
} as const;
