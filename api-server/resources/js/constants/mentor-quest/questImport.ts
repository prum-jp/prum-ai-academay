/**
 * CSV 列定義（quests テーブル対応）。
 *
 * | CSV列           | DB / アプリ                    |
 * |----------------|-------------------------------|
 * | No             | クエスト sort_order（優先）      |
 * | Unit           | ユニット sort_order             |
 * | Quest          | クエスト sort_order（No 空時）   |
 * | MUST           | is_required（◯=必須、空欄=任意） |
 * | Todo           | プレビュー表示のみ               |
 * | Unit名         | ユニット title                  |
 * | Quest名        | クエスト title                  |
 * | 概要           | overview（列そのまま保持）       |
 * | 目的           | purpose（列そのまま保持）        |
 * | 提出物         | deliverable（列そのまま保持）    |
 * | ツール         | tool                          |
 * | レベル         | difficulty（★1〜5）            |
 * | 64             | 無視                           |
 * | 完了条件       | completionCondition           |
 * | ビジネス戦闘力等 | skillGrants（値ありで付与）     |
 * | ヒューマン戦闘力等 | skillGrants（値ありで付与）   |
 * | コンセプチュアル戦闘力等 | skillGrants（値ありで付与） |
 *
 * Quest名が空の行はユニット定義（Unit名 + Unit）のみ。
 */
export const questImportCsvColumns = [
    'No',
    'Unit',
    'Quest',
    'MUST',
    'Todo',
    'Unit名',
    'Quest名',
    '概要',
    '目的',
    '提出物',
    'ツール',
    'レベル',
    '64',
    '完了条件',
    'ビジネス戦闘力',
    'ヒューマン戦闘力',
    'コンセプチュアル戦闘力',
] as const;

export const questImportActionLabels = {
    create: '新規',
    update: '更新',
    unchanged: '変化なし',
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
    defaultQuestTierLabel: 'クエスト段階',
    defaultQuestTierHint:
        'CSVにクエスト段階列がない場合、新規登録行にのみここで選んだ段階（低・中・高・エキスパート）を適用します。既存クエストの段階は維持されます。',
    uploadHint:
        'UTF-8 の CSV を選択してください。並び順はクエストが No、ユニットが Unit（なければ最小 No）に対応します。概要などにリンクを入れる場合は、スプレッドシートの「リンク挿入」ではなく [表示名](URL) とセルに直接入力してください（CSV では URL が失われます）。ツール列に複数指定する場合は「Gemini, Googleスプレッドシート」のようにカンマ区切りにしてください。',
    cancelLabel: 'キャンセル',
    backLabel: 'ファイル選択に戻る',
    assignLabel: '割り当てする',
    assigningLabel: '割り当て中...',
    assignModalDescription:
        'CSVの内容を登録し、個人ユニットをどの受講生に割り当てるか選んでください。',
    previewLoadingLabel: 'プレビューを生成しています...',
} as const;

export const questImportMessages = {
    previewFailed: 'プレビューの生成に失敗しました。',
    applyFailed: '一括登録の反映に失敗しました。',
    assignFailed: '一括登録後の割り当てに失敗しました。',
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
    todoNote: 'Todo',
    unitTitle: 'Unit名',
    title: 'Quest名',
    overview: '概要',
    description: '概要',
    purpose: '目的',
    deliverable: '提出物',
    completionCondition: '完了条件',
    clearCondition: '完了条件',
    toolCode: 'ツール',
    difficulty: 'レベル',
    experiencePoints: 'XP',
    questTier: 'クエスト段階',
    isRequired: 'MUST',
    childQuests: '含まれるクエスト',
    expandLabel: '編集/詳細',
    collapseLabel: '閉じる',
} as const;
