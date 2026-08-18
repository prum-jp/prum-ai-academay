import type { QuestSubmissionType } from '@/constants/quest/questSubmission';

export interface QuestSubmissionFile {
    id: number;
    url: string | null;
}

export interface QuestSubmission {
    type: QuestSubmissionType;
    url: string | null;
    text: string | null;
    files?: QuestSubmissionFile[];
}

export interface QuestSubmissionPayload {
    type: QuestSubmissionType;
    url?: string;
    text?: string;
    file?: File;
}
