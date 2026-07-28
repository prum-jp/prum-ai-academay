import axios from 'axios';

interface LaravelValidationErrorResponse {
    errors?: Record<string, string[]>;
}

export const extractApiFieldErrors = (
    error: unknown,
    fields: readonly string[],
): Record<string, string> => {
    if (!axios.isAxiosError(error)) {
        return {};
    }

    const data = error.response?.data as LaravelValidationErrorResponse | undefined;
    const apiErrors = data?.errors ?? {};
    const next: Record<string, string> = {};

    for (const field of fields) {
        const message = apiErrors[field]?.[0];
        if (message) {
            next[field] = message;
        }
    }

    return next;
};
