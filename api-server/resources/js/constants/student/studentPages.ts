export interface StudentPageItem {
    name: string;
    path: string;
    label: string;
    windowTitle: string;
    subtitle: string;
    icon: string;
    singleColumn: boolean;
    plainContent: boolean;
}

/** アカデミー生画面のページ順（左右矢印で巡回） */
export const studentPages: StudentPageItem[] = [
    {
        name: 'student-sheet',
        path: '/',
        label: '受講者シート',
        windowTitle: '受講者シート',
        subtitle: 'PRUM AI ACADEMY / STUDENT SHEET',
        icon: 'fa-solid fa-shield-halved',
        singleColumn: false,
        plainContent: false,
    },
    {
        name: 'student-quests',
        path: '/quests',
        label: 'クエスト一覧',
        windowTitle: 'クエスト一覧',
        subtitle: 'PRUM AI ACADEMY / QUEST LIST',
        icon: 'fa-solid fa-book-open',
        singleColumn: true,
        plainContent: true,
    },
    // TODO: 後に機能追加 — 実績バッジ（スキルブック）ページ
    // {
    //     name: 'student-skillbook',
    //     path: '/skillbook',
    //     label: '実績バッジ',
    //     windowTitle: '実績バッジ',
    //     subtitle: 'PRUM AI ACADEMY / ACHIEVEMENT BADGES',
    //     icon: 'fa-solid fa-medal',
    //     singleColumn: true,
    //     plainContent: false,
    // },
    {
        name: 'student-directory',
        path: '/students',
        label: 'アカデミー生一覧',
        windowTitle: 'アカデミー生一覧',
        subtitle: 'PRUM AI ACADEMY / ADVENTURER DIRECTORY',
        icon: 'fa-solid fa-users',
        singleColumn: true,
        plainContent: false,
    },
];
