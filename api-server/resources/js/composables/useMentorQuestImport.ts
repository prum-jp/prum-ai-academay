import { computed, ref } from 'vue';
import type { QuestImportItem, QuestImportMeta } from '@/types/questImport';
import { applyQuestImport, previewQuestImport } from '@/api/questImport';
import { questImportMessages } from '@/constants/questImport';
import {
    applyPreviewResponse,
    computeQuestImportMeta,
    groupImportItemsByUnit,
    parseQuestImportCsv,
    refreshImportMetaCounts,
    setAllItemsPublished,
    syncUnitPublishToItems,
    toImportPayload,
} from '@/utils/questImport';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

export function useMentorQuestImport() {
    const step = ref<'upload' | 'preview'>('upload');
    const items = ref<QuestImportItem[]>([]);
    const meta = ref<QuestImportMeta | null>(null);
    const parseErrors = ref<string[]>([]);
    const errorMessage = ref('');
    const isLoading = ref(false);
    const isApplying = ref(false);
    const selectedFileName = ref('');

    const hasBlockingErrors = computed((): boolean => {
        if (parseErrors.value.length > 0) {
            return true;
        }

        return items.value.some((item) => (item.errors?.length ?? 0) > 0);
    });

    const reset = (): void => {
        step.value = 'upload';
        items.value = [];
        meta.value = null;
        parseErrors.value = [];
        errorMessage.value = '';
        isLoading.value = false;
        isApplying.value = false;
        selectedFileName.value = '';
    };

    const loadCsvFile = async (file: File): Promise<boolean> => {
        errorMessage.value = '';
        parseErrors.value = [];
        isLoading.value = true;
        selectedFileName.value = file.name;

        try {
            const text = await file.text();
            const parsed = parseQuestImportCsv(text);
            parseErrors.value = parsed.errors;

            if (parsed.items.length === 0) {
                errorMessage.value = parsed.errors[0] ?? questImportMessages.emptyFile;
                return false;
            }

            const preview = await previewQuestImport(toImportPayload(parsed.items));
            items.value = applyPreviewResponse(preview, parsed.items, true);
            meta.value = preview.meta;
            step.value = 'preview';

            return true;
        } catch (error: unknown) {
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                questImportMessages.previewFailed,
            );
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    const refreshPreview = async (): Promise<void> => {
        errorMessage.value = '';
        isLoading.value = true;

        try {
            const preview = await previewQuestImport(toImportPayload(items.value));
            items.value = applyPreviewResponse(preview, items.value);
            meta.value = preview.meta;
        } catch (error: unknown) {
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                questImportMessages.previewFailed,
            );
        } finally {
            isLoading.value = false;
        }
    };

    const removeItem = (clientId: string): void => {
        items.value = groupImportItemsByUnit(
            items.value.filter((item) => item.clientId !== clientId),
        );
        meta.value = computeQuestImportMeta(items.value);
    };

    const setAllPublished = (isPublished: boolean): void => {
        items.value = setAllItemsPublished(items.value, isPublished);
        meta.value = refreshImportMetaCounts(items.value, meta.value);
    };

    const syncUnitPublish = (unitTitle: string, isPublished: boolean): void => {
        items.value = syncUnitPublishToItems(items.value, unitTitle, isPublished);
        meta.value = refreshImportMetaCounts(items.value, meta.value);
    };

    const apply = async (): Promise<boolean> => {
        if (items.value.length === 0) {
            errorMessage.value = questImportMessages.emptyFile;
            return false;
        }

        await refreshPreview();

        if (hasBlockingErrors.value) {
            errorMessage.value = questImportMessages.hasErrors;
            return false;
        }

        errorMessage.value = '';
        isApplying.value = true;

        try {
            await applyQuestImport(toImportPayload(items.value));
            return true;
        } catch (error: unknown) {
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                questImportMessages.applyFailed,
            );
            return false;
        } finally {
            isApplying.value = false;
        }
    };

    return {
        step,
        items,
        meta,
        parseErrors,
        errorMessage,
        isLoading,
        isApplying,
        selectedFileName,
        hasBlockingErrors,
        reset,
        loadCsvFile,
        removeItem,
        setAllPublished,
        syncUnitPublish,
        apply,
    };
}
