import type { QuestProgressStatus } from '@/constants/questProgress';
import {
    getQuestProgressActionsForRole,
    questProgressStatuses,
    questProgressStatusLabels,
} from '@/constants/questProgress';

export const getSelectableQuestProgressStatuses = (
    current: QuestProgressStatus,
    role: 'student' | 'mentor' = 'student',
): QuestProgressStatus[] => getQuestProgressActionsForRole(current, role);

export const questProgressStatusShortLabels: Record<QuestProgressStatus, string> = {
    not_started: '未着手',
    in_progress: '着手中',
    review_requested: 'レビュー',
    rejected: '差戻',
    completed: '完了',
};

const questProgressStatusClassMap: Record<QuestProgressStatus, string> = {
    not_started: 'is-not-started',
    in_progress: 'is-in-progress',
    review_requested: 'is-review-requested',
    rejected: 'is-rejected',
    completed: 'is-completed',
};

export const getQuestProgressStatusLabel = (status: QuestProgressStatus): string =>
    questProgressStatusLabels[status];

export const getQuestProgressStatusShortLabel = (status: QuestProgressStatus): string =>
    questProgressStatusShortLabels[status];

export const getQuestProgressStatusClass = (status: QuestProgressStatus): string =>
    questProgressStatusClassMap[status];

export const isQuestProgressCompleted = (status: QuestProgressStatus): boolean =>
    status === 'completed';

export interface QuestProgressSelectOption {
    value: QuestProgressStatus;
    label: string;
    selectable: boolean;
}

export const buildQuestProgressSelectOptions = (
    current: QuestProgressStatus,
    role: 'student' | 'mentor' = 'student',
): QuestProgressSelectOption[] => {
    const selectable = new Set(getSelectableQuestProgressStatuses(current, role));

    if (role === 'mentor') {
        return [...selectable].map((value) => ({
            value,
            label: questProgressStatusLabels[value],
            selectable: true,
        }));
    }

    return questProgressStatuses.map((value) => ({
        value,
        label: questProgressStatusLabels[value],
        selectable: selectable.has(value),
    }));
};
