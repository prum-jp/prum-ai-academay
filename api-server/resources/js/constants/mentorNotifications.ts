export const mentorNotificationsPageConfig = {
    title: '通知一覧',
    icon: 'fa-solid fa-bell',
} as const;

export const mentorNotificationsConfig = {
    cardTitle: 'レビュー依頼',
    cardIcon: 'fa-solid fa-inbox',
    columns: {
        name: '名前',
        type: '通知の種類',
        requestedAt: '依頼日',
    },
    empty: 'レビュー依頼はありません。',
    loading: '通知を読み込んでいます...',
    loadFailed: '通知一覧の取得に失敗しました。',
    navLabel: '通知',
} as const;
