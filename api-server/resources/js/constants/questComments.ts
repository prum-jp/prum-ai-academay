export const questCommentsConfig = {
    title: 'コメント・履歴',
    empty: 'まだコメントや履歴はありません。',
    placeholder: 'コメントを入力...',
    submitLabel: 'コメントする',
    submittingLabel: '送信中...',
    loadFailed: 'コメントの取得に失敗しました。',
    submitFailed: 'コメントの送信に失敗しました。',
    roleLabels: {
        mentor: 'メンター',
        student: 'アカデミー生',
    },
    activity: {
        statusChanged: (fromLabel: string, toLabel: string): string =>
            `ステータスを「${fromLabel}」から「${toLabel}」に変更しました`,
        submissionAdded: '提出物を提出しました',
        openSubmissionLink: '提出物を開く',
    },
} as const;
