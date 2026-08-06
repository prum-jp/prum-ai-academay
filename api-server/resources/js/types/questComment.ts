import type { QuestProgressStatus } from '@/constants/questProgress';
import type { QuestSubmissionType } from '@/constants/questSubmission';

export type QuestCommentAuthorRole = 'student' | 'mentor';

export type QuestActivityType = 'comment' | 'status_changed' | 'submission_added';

export interface QuestActivityStatusMetadata {
    fromStatus: QuestProgressStatus;
    toStatus: QuestProgressStatus;
}

export interface QuestActivitySubmissionMetadata {
    type?: QuestSubmissionType;
    url?: string;
    text?: string;
}

export type QuestActivityMetadata =
    | QuestActivityStatusMetadata
    | QuestActivitySubmissionMetadata;

export interface QuestComment {
    id: number;
    type: QuestActivityType;
    body: string | null;
    metadata: QuestActivityMetadata | null;
    authorId: number;
    authorName: string;
    authorRole: QuestCommentAuthorRole;
    authorAvatarUrl: string | null;
    createdAt: string;
    isOwn: boolean;
}

export interface QuestCommentListResponse {
    data: QuestComment[];
}

export const isStatusActivityMetadata = (
    metadata: QuestActivityMetadata | null,
): metadata is QuestActivityStatusMetadata =>
    metadata !== null && 'fromStatus' in metadata && 'toStatus' in metadata;

export const isSubmissionActivityMetadata = (
    metadata: QuestActivityMetadata | null,
): metadata is QuestActivitySubmissionMetadata =>
    metadata !== null && ('url' in metadata || 'text' in metadata || 'type' in metadata);
