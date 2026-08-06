import type { QuestSubmissionType } from '@/constants/questSubmission';

export interface QuestSubmission {
    type: QuestSubmissionType;
    url: string | null;
    text: string | null;
}

export interface QuestSubmissionPayload {
    type: QuestSubmissionType;
    url?: string;
    text?: string;
    file?: File;
}
