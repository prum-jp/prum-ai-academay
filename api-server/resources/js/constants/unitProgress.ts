import type { QuestItem } from '@/types/quest';

export const unitProgressStatuses = [
    'not_started',
    'in_progress',
    'has_rejected',
    'completed',
] as const;

export type UnitProgressStatus = (typeof unitProgressStatuses)[number];

export const unitProgressFilters = ['all', 'in_progress', 'completed', 'not_started'] as const;

export type UnitProgressFilter = (typeof unitProgressFilters)[number];

export const DEFAULT_UNIT_PROGRESS_FILTER: UnitProgressFilter = 'all';

export const unitProgressFilterLabels: Record<UnitProgressFilter, string> = {
    all: 'すべて',
    in_progress: '着手中のみ',
    completed: '完了のみ',
    not_started: '未着手のみ',
};

export const unitProgressFilterOptions = unitProgressFilters.map((value) => ({
    value,
    label: unitProgressFilterLabels[value],
}));

export const matchesUnitProgressFilter = (
    status: UnitProgressStatus,
    filter: UnitProgressFilter,
): boolean => {
    if (filter === 'all') {
        return true;
    }

    if (filter === 'in_progress') {
        return status === 'in_progress' || status === 'has_rejected';
    }

    if (filter === 'completed') {
        return status === 'completed';
    }

    return status === 'not_started';
};

export const unitProgressStatusLabels: Record<UnitProgressStatus, string> = {
    not_started: '未着手',
    in_progress: '着手中',
    has_rejected: '差し戻しあり',
    completed: '完了',
};

export const resolveUnitProgressStatus = (quests: QuestItem[]): UnitProgressStatus => {
    if (quests.length === 0) {
        return 'not_started';
    }

    const statuses = quests.map((quest) => quest.progressStatus);

    if (statuses.every((status) => status === 'completed')) {
        return 'completed';
    }

    if (statuses.some((status) => status === 'rejected')) {
        return 'has_rejected';
    }

    if (statuses.some((status) => status === 'in_progress')) {
        return 'in_progress';
    }

    if (statuses.some((status) => status === 'review_requested')) {
        return 'in_progress';
    }

    if (statuses.some((status) => status === 'completed')) {
        return 'in_progress';
    }

    return 'not_started';
};

const unitProgressStatusClassMap: Record<UnitProgressStatus, string> = {
    not_started: 'is-not-started',
    in_progress: 'is-in-progress',
    has_rejected: 'is-rejected',
    completed: 'is-completed',
};

export const getUnitProgressStatusLabel = (status: UnitProgressStatus): string =>
    unitProgressStatusLabels[status];

export const getUnitProgressStatusClass = (status: UnitProgressStatus): string =>
    unitProgressStatusClassMap[status];

export const isUnitProgressCompleted = (status: UnitProgressStatus): boolean =>
    status === 'completed';
