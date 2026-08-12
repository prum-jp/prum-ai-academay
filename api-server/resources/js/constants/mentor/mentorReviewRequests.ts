export const mentorReviewRequestsPageConfig = {
    title: 'レビュー依頼一覧',
    icon: 'fa-solid fa-inbox',
} as const;

export const mentorReviewRequestsConfig = {
    cardTitle: 'レビュー依頼',
    cardIcon: 'fa-solid fa-inbox',
    columns: {
        name: '名前',
        type: '通知の種類',
        requestedAt: '依頼日',
    },
    empty: 'レビュー依頼はありません。',
    loading: 'レビュー依頼を読み込んでいます...',
    loadFailed: 'レビュー依頼一覧の取得に失敗しました。',
    navLabel: 'レビュー依頼',
} as const;
