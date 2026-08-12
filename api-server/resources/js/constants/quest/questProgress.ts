export const questProgressStatuses = [
    'not_started',
    'in_progress',
    'review_requested',
    'rejected',
    'completed',
] as const;

export type QuestProgressStatus = (typeof questProgressStatuses)[number];

export const questProgressStatusLabels: Record<QuestProgressStatus, string> = {
    not_started: '未着手',
    in_progress: '着手中',
    review_requested: 'レビュー依頼中',
    rejected: '差し戻し',
    completed: '完了',
};

export const getQuestProgressActions = (
    current: QuestProgressStatus,
): QuestProgressStatus[] => {
    switch (current) {
        case 'not_started':
            return ['in_progress'];
        case 'in_progress':
            return ['review_requested', 'not_started'];
        case 'review_requested':
            return ['in_progress'];
        case 'rejected':
            return ['in_progress'];
        case 'completed':
        default:
            return [];
    }
};

export const getMentorQuestProgressActions = (
    current: QuestProgressStatus,
): QuestProgressStatus[] =>
    questProgressStatuses.filter((status) => status !== current);

export const getQuestProgressActionsForRole = (
    current: QuestProgressStatus,
    role: 'student' | 'mentor',
): QuestProgressStatus[] =>
    role === 'mentor'
        ? getMentorQuestProgressActions(current)
        : getQuestProgressActions(current);
