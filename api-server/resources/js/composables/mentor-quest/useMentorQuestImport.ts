import { computed, ref } from 'vue';
import type { QuestImportApplyResult, QuestImportItem, QuestImportMeta } from '@/types/mentor-quest/questImport';
import { applyQuestImport, previewQuestImport } from '@/api/mentor-quest/questImport';
import { questImportMessages } from '@/constants/mentor-quest/questImport';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/quest/questTier';
import {
    applyPreviewResponse,
    computeQuestImportMeta,
    groupImportItemsByUnit,
    parseQuestImportCsv,
    toImportPayload,
} from '@/utils/mentor-quest/questImport';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

export function useMentorQuestImport() {
    const step = ref<'upload' | 'preview'>('upload');
    const items = ref<QuestImportItem[]>([]);
    const meta = ref<QuestImportMeta | null>(null);
    const parseErrors = ref<string[]>([]);
    const errorMessage = ref('');
    const isLoading = ref(false);
    const isApplying = ref(false);
    const selectedFileName = ref('');
    const defaultQuestTier = ref<QuestTier>(DEFAULT_QUEST_TIER);

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
        defaultQuestTier.value = DEFAULT_QUEST_TIER;
    };

    const loadCsvFile = async (file: File, questTier: QuestTier = defaultQuestTier.value): Promise<boolean> => {
        defaultQuestTier.value = questTier;
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

            const preview = await previewQuestImport(
                toImportPayload(parsed.items),
                defaultQuestTier.value,
            );
            items.value = applyPreviewResponse(preview, parsed.items);
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
            const preview = await previewQuestImport(
                toImportPayload(items.value),
                defaultQuestTier.value,
            );
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

    const apply = async (): Promise<QuestImportApplyResult[] | null> => {
        if (items.value.length === 0) {
            errorMessage.value = questImportMessages.emptyFile;
            return null;
        }

        await refreshPreview();

        if (hasBlockingErrors.value) {
            errorMessage.value = questImportMessages.hasErrors;
            return null;
        }

        errorMessage.value = '';
        isApplying.value = true;

        try {
            const response = await applyQuestImport(
                toImportPayload(items.value),
                defaultQuestTier.value,
            );
            return response.data;
        } catch (error: unknown) {
            errorMessage.value = extractApiErrorMessage(
                error,
                undefined,
                questImportMessages.applyFailed,
            );
            return null;
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
        defaultQuestTier,
        hasBlockingErrors,
        reset,
        loadCsvFile,
        removeItem,
        apply,
    };
}
