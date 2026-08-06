export const mentorAssignmentMessages = {
    loadFailed: 'カリキュラム設定の取得に失敗しました。',
    saveFailed: 'カリキュラム設定の保存に失敗しました。',
    saveSuccess: 'カリキュラムを反映しました！',
    saving: '保存中...',
    saveLabel: '反映する',
    loading: 'カリキュラム設定を読み込んでいます...',
    emptyCurricula: '登録されているカリキュラムはまだありません。',
    emptyUnits: '登録されている個人ユニットはまだありません。',
    curriculumSectionTitle: 'カリキュラム単位',
    unitSectionTitle: 'ユニット単位（個別追加）',
    viaCurriculumNote: 'カリキュラム経由で反映中',
    assignmentNote: '割り当てたユニット・カリキュラムだけが、受講生のクエスト一覧に表示されます。',
    assignAllStudents: '全員に反映',
    assignAllStudentsSuccess: (count: number): string =>
        count > 0 ? `${count}人の受講生に新規反映しました。` : '全員に既に反映済みです。',
    assignAllStudentsFailed: '全員への反映に失敗しました。',
    assignSelectedStudentsFailed: '選択した受講生への反映に失敗しました。',
} as const;

export const mentorCurriculumMessages = {
    loadFailed: 'カリキュラム一覧の取得に失敗しました。',
    saveFailed: 'カリキュラムの保存に失敗しました。',
    deleteFailed: 'カリキュラムの削除に失敗しました。',
    createSuccess: 'カリキュラムを作成しました！',
    updateSuccess: 'カリキュラムを更新しました！',
    deleteSuccess: 'カリキュラムを削除しました。',
    emptyList: 'カリキュラムはまだありません。',
    loading: 'カリキュラムを読み込んでいます...',
    createButtonLabel: 'カリキュラム追加',
    editButtonLabel: '編集',
    deleteButtonLabel: '削除',
    saveLabel: '保存する',
    savingLabel: '保存中...',
    createTitle: 'カリキュラムを作成',
    editTitle: 'カリキュラムを編集',
    nameLabel: 'カリキュラム名',
    descriptionLabel: '説明',
    unitsLabel: '含める個人ユニット',
    namePlaceholder: '例：2026春 Pythonコース',
    descriptionPlaceholder: '任意の説明',
    assignmentSectionTitle: '反映する受講生',
    assignmentTargetAll: '全員',
    assignmentTargetSelected: '個別に選ぶ',
    assignmentSelectedEmpty: '条件に合う受講生が見つかりませんでした。',
    assignmentSelectedRequired: '個別選択の場合は、1人以上選んでください。',
    assignmentSearchEmpty: '登録されている受講生はまだいません。',
} as const;

export const mentorStudentAssignmentCardConfig = {
    buttonLabel: 'カリキュラム設定',
    modalTitle: 'カリキュラムを反映',
    modalIcon: 'fa-solid fa-book-open',
} as const;

export const mentorCurriculumBoardConfig = {
    title: 'カリキュラム',
    icon: 'fa-solid fa-layer-group',
    description: '複数の個人ユニットをまとめて、受講生に一括で反映できます。',
} as const;
