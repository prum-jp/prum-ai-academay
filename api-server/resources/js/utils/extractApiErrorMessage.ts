import axios from 'axios';

interface LaravelValidationErrorResponse {
    message?: string;
    errors?: Record<string, string[]>;
}

export const extractApiErrorMessage = (
    error: unknown,
    field?: string,
    fallback = '処理に失敗しました。',
): string => {
    if (!axios.isAxiosError(error)) {
        return fallback;
    }

    const data = error.response?.data as LaravelValidationErrorResponse | undefined;
    const fieldErrors = field ? data?.errors?.[field] : undefined;

    if (Array.isArray(fieldErrors) && fieldErrors[0]) {
        return fieldErrors[0];
    }

    if (!field && data?.errors) {
        const allMessages = Object.values(data.errors).flat();
        if (allMessages.length > 0) {
            return allMessages.join(' ');
        }
    }

    if (typeof data?.message === 'string' && data.message.length > 0) {
        return data.message;
    }

    return fallback;
};
