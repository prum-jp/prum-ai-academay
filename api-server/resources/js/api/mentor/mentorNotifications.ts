import axios from 'axios';
import type { MentorReviewRequestListResponse } from '@/types/mentor/mentorNotifications';

export const fetchMentorReviewRequests = async (): Promise<MentorReviewRequestListResponse> => {
    const { data } = await axios.get<MentorReviewRequestListResponse>(
        '/api/mentor/review-requests',
    );

    return data;
};
