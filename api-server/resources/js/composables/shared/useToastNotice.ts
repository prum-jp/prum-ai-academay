import { ref } from 'vue';

const DEFAULT_DURATION_MS = 1200;

export interface ToastNoticeOptions {
    onDismiss?: () => void;
}

export function useToastNotice(durationMs = DEFAULT_DURATION_MS) {
    const showToast = ref(false);
    const toastMessage = ref('');

    const showNotice = (message: string, options?: ToastNoticeOptions): void => {
        toastMessage.value = message;
        showToast.value = true;

        window.setTimeout(() => {
            showToast.value = false;
            options?.onDismiss?.();
        }, durationMs);
    };

    return {
        showToast,
        toastMessage,
        showNotice,
    };
}
