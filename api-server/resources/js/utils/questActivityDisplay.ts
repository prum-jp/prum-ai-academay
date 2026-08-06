import type { QuestComment } from '@/types/questComment';
import {
    isStatusActivityMetadata,
    isSubmissionActivityMetadata,
} from '@/types/questComment';
import { questCommentsConfig } from '@/constants/questComments';
import { questProgressStatusLabels } from '@/constants/questProgress';
import type { QuestProgressStatus } from '@/constants/questProgress';
import { questSubmissionTypeLabels } from '@/constants/questSubmission';

const getStatusLabel = (status: QuestProgressStatus): string =>
    questProgressStatusLabels[status] ?? status;

export const isQuestActivity = (item: QuestComment): boolean => item.type !== 'comment';

export const formatQuestActivityText = (item: QuestComment): string => {
    if (item.type === 'status_changed' && isStatusActivityMetadata(item.metadata)) {
        return questCommentsConfig.activity.statusChanged(
            getStatusLabel(item.metadata.fromStatus),
            getStatusLabel(item.metadata.toStatus),
        );
    }

    if (item.type === 'submission_added' && isSubmissionActivityMetadata(item.metadata)) {
        const typeLabel = item.metadata.type
            ? questSubmissionTypeLabels[item.metadata.type]
            : questSubmissionTypeLabels.link;

        return `${questCommentsConfig.activity.submissionAdded}（${typeLabel}）`;
    }

    return item.body ?? '';
};

export const getQuestActivityLink = (item: QuestComment): string | null => {
    if (item.type === 'submission_added' && isSubmissionActivityMetadata(item.metadata)) {
        return item.metadata.url ?? null;
    }

    return null;
};

export const getQuestActivityIcon = (item: QuestComment): string => {
    switch (item.type) {
        case 'status_changed':
            return 'fa-solid fa-arrows-rotate';
        case 'submission_added':
            if (isSubmissionActivityMetadata(item.metadata) && item.metadata.type) {
                switch (item.metadata.type) {
                    case 'image':
                        return 'fa-solid fa-image';
                    case 'video':
                        return 'fa-solid fa-video';
                    case 'audio':
                        return 'fa-solid fa-volume-high';
                    case 'text':
                        return 'fa-solid fa-align-left';
                    case 'link':
                    default:
                        return 'fa-solid fa-link';
                }
            }

            return 'fa-solid fa-link';
        default:
            return 'fa-solid fa-comment';
    }
};

export const getQuestActivityPreviewText = (item: QuestComment): string | null => {
    if (item.type !== 'submission_added' || !isSubmissionActivityMetadata(item.metadata)) {
        return null;
    }

    if (item.metadata.type === 'text' && item.metadata.text?.trim()) {
        return item.metadata.text.trim();
    }

    return null;
};
