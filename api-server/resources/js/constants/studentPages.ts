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
        label: 'キャラ装備シート',
        windowTitle: '勇者のキャラ装備シート',
        subtitle: 'PRUM AI ACADEMY / CHARACTER SHEET',
        icon: 'fa-solid fa-shield-halved',
        singleColumn: false,
        plainContent: false,
    },
    {
        name: 'student-quests',
        path: '/quests',
        label: '冒険クエスト手帳',
        windowTitle: '冒険クエスト手帳',
        subtitle: 'PRUM AI ACADEMY / QUEST NOTEBOOK',
        icon: 'fa-solid fa-book-open',
        singleColumn: true,
        plainContent: true,
    },
    {
        name: 'student-skillbook',
        path: '/skillbook',
        label: '実績バッジ',
        windowTitle: '実績バッジ',
        subtitle: 'PRUM AI ACADEMY / ACHIEVEMENT BADGES',
        icon: 'fa-solid fa-medal',
        singleColumn: true,
        plainContent: false,
    },
];
