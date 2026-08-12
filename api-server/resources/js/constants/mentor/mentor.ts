import { ROLE_MENTOR, ROLE_STUDENT } from '@/types/shared/auth';

export const mentorPanelConfig = {
    subtitle: 'PRUM AI ACADEMY / MENTOR PANEL',
} as const;

export const mentorStudentRegisterPageConfig = {
    title: 'ユーザー新規登録',
    icon: 'fa-solid fa-user-plus',
} as const;

export const mentorStudentRegisterCardConfig = {
    title: '新しいユーザーを登録',
    icon: 'fa-solid fa-scroll',
    description: '名前・メール・パスワード・ロールを設定して登録します。',
} as const;

export const mentorStudentSearchConfig = {
    label: '受講生を検索',
    placeholder: '名前・メールで検索',
    buttonLabel: '検索',
    buttonIcon: 'fa-solid fa-magnifying-glass',
} as const;

export const mentorStudentMessages = {
    loadFailed: '学習者一覧の取得に失敗しました。',
    selectFailed: '学習者の選択に失敗しました。',
    registerFailed: 'ユーザーの登録に失敗しました。',
    registerSuccessToast: 'ユーザーを登録しました！',
    emptyList: '登録されている学習者はまだいません。',
    emptySearch: '条件に合う学習者が見つかりませんでした。',
    loading: '学習者一覧を読み込んでいます...',
    registerNote: '※パスワードは8文字以上で設定してください。',
    registerSubmitLabel: '登録する',
    registerSubmittingLabel: '登録中...',
} as const;

export const mentorStudentFormLabels = {
    name: '受講者名',
    email: 'メールアドレス',
    password: 'パスワード',
    passwordConfirmation: 'パスワード（確認）',
    role: 'ロール',
} as const;

export const mentorStudentFormPlaceholders = {
    name: '例：山田太郎',
    email: '例：student@prum.local',
    password: '8文字以上',
    passwordConfirmation: 'もう一度入力',
} as const;

export const mentorStudentRoleOptions = [
    { value: ROLE_STUDENT, label: 'アカデミー生' },
    { value: ROLE_MENTOR, label: 'メンター' },
] as const;
