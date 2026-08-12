export const mentorToolPageConfig = {
    title: 'AIマスタ追加',
    icon: 'fa-solid fa-wand-magic-sparkles',
} as const;

export const mentorToolBoardCardConfig = {
    title: 'AIツールマスタ',
    icon: 'fa-solid fa-toolbox',
    description:
        'クエストで選択できるAIツールを登録・管理します。子クエストの「使用ツール」から選べるようになります。',
    createButtonLabel: '新規追加',
} as const;

export const mentorToolCreateModalConfig = {
    title: 'AIツールを追加',
    icon: 'fa-solid fa-plus',
    codeLabel: '識別コード',
    nameLabel: '表示名',
    cancelLabel: 'キャンセル',
    submitLabel: '追加する',
    submittingLabel: '追加中...',
} as const;

export const mentorToolMessages = {
    loadFailed: 'AIツール一覧の取得に失敗しました。',
    createFailed: 'AIツールの登録に失敗しました。',
    createSuccessToast: 'AIツールを追加しました！',
    emptyList: '登録されているAIツールはまだありません。',
    loading: 'AIツールを読み込んでいます...',
} as const;

export const mentorToolFormPlaceholders = {
    code: '例：gemini',
    name: '例：Gemini',
} as const;

export const mentorToolFormNote = '識別コードは英数字・ハイフン・アンダースコアのみ使用できます。';

export const mentorToolSelectConfig = {
    empty: '登録されているAIツールがありません。',
    addLinkLabel: 'AIマスタで追加する',
} as const;
