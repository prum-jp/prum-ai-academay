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

export const mentorToolFormModalConfig = {
    createTitle: 'AIツールを追加',
    editTitle: 'AIツールを編集',
    icon: 'fa-solid fa-plus',
    editIcon: 'fa-solid fa-pen',
    nameLabel: '表示名',
    cancelLabel: 'キャンセル',
    createSubmitLabel: '追加する',
    editSubmitLabel: '保存する',
    submittingLabel: '保存中...',
    editButtonLabel: '編集',
} as const;

export const mentorToolMessages = {
    loadFailed: 'AIツール一覧の取得に失敗しました。',
    createFailed: 'AIツールの登録に失敗しました。',
    updateFailed: 'AIツールの更新に失敗しました。',
    createSuccessToast: 'AIツールを追加しました！',
    updateSuccessToast: 'AIツールを更新しました！',
    emptyList: '登録されているAIツールはまだありません。',
    loading: 'AIツールを読み込んでいます...',
} as const;

export const mentorToolFormPlaceholders = {
    name: '例：Gemini',
} as const;

export const mentorToolFormNote = '表示名が同じツールは登録できません（大文字・小文字は区別しません）。';

export const mentorToolSelectConfig = {
    empty: '登録されているAIツールがありません。',
    addLinkLabel: 'AIマスタで追加する',
} as const;
