import type { QuestType } from '@/types/quest/quest';

export interface QuestSectionDefinition {
    type: QuestType;
    title: string;
    icon: string;
}

export const questSectionDefinitions: QuestSectionDefinition[] = [
    {
        type: 'personal',
        title: '個人クエスト',
        icon: 'fa-solid fa-seedling',
    },
    {
        type: 'team',
        title: 'チームクエスト',
        icon: 'fa-solid fa-handshake-angle',
    },
    {
        type: 'special',
        title: '特別クエスト',
        icon: 'fa-solid fa-mountain',
    },
];

export const questMessages = {
    loadQuestsFailed: 'クエストの取得に失敗しました。',
    loadUnitsFailed: 'ユニットの取得に失敗しました。',
    emptyQuests: 'クエストはまだありません。',
    emptyUnits: 'ユニットはまだありません。',
    emptyFilteredUnits: '条件に一致するユニットがありません。',
    unitProgressFilterLabel: '表示',
    loading: '読み込み中...',
} as const;

export const questDetailConfig = {
    modalIcon: 'fa-solid fa-scroll',
    fallbackTitle: 'クエスト詳細',
    closeLabel: '閉じる',
    sections: {
        content: {
            title: 'クエスト内容',
            icon: 'fa-solid fa-book-open',
        },
        rewards: {
            title: '報酬',
            icon: 'fa-solid fa-star',
        },
        facts: {
            title: '条件・期間',
            icon: 'fa-solid fa-circle-info',
        },
    },
    emptyDescription: '詳細はまだ書かれていません。',
    emptyRewards: '報酬情報はありません。',
} as const;

export const questUnitConfig = {
    fallbackTitle: 'ユニット詳細',
    progressLabel: (completed: number, total: number): string => `${completed}/${total} 達成`,
    clearConditionTitle: 'クエストクリア（完了）条件',
    emptyClearCondition: '完了条件は設定されていません。',
    emptyQuests: '紐づくクエストはまだありません。',
    defaultToolIcon: 'fa-solid fa-wand-magic-sparkles',
} as const;

/** ユニットモーダル用のスキル表示名（後方互換） */
export { skillLabels as growthStatLabels } from '@/constants/shared/skills';

export type NonPersonalQuestType = Exclude<QuestType, 'personal'>;

export const nonPersonalSectionDefinitions = questSectionDefinitions.filter(
    (definition): definition is QuestSectionDefinition & { type: NonPersonalQuestType } =>
        definition.type !== 'personal',
);

export const personalSectionDefinition = questSectionDefinitions.find(
    (definition) => definition.type === 'personal',
);
