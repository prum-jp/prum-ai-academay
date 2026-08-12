export const studentDirectoryPageConfig = {
    title: 'アカデミー生一覧',
    icon: 'fa-solid fa-users',
    description: '受講生を選ぶと他の受講者情報を回覧できます。',
} as const;

export const studentDirectorySearchConfig = {
    label: '受講生を検索',
    placeholder: '名前で検索',
    buttonLabel: '検索',
    buttonIcon: 'fa-solid fa-magnifying-glass',
} as const;

export const studentDirectoryMessages = {
    loadFailed: 'アカデミー生一覧の取得に失敗しました。',
    profileLoadFailed: '受講者情報の取得に失敗しました。',
    emptyList: '登録されている受講生はまだいません。',
    emptySearch: '条件に合う受講生が見つかりませんでした。',
    loading: '受講生一覧を読み込んでいます...',
    profileLoading: '受講者情報を読み込んでいます...',
    backToDirectory: 'アカデミー生一覧に戻る',
    nextStudentLinkSuffix: 'さんのページに移動',
    copyAdventurerCardSuccessToast: '受講者カードをコピーしました！',
} as const;
