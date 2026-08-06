import { computed, ref } from 'vue';
import { downloadMentorQuestMasterCsv, fetchMentorQuestMaster } from '@/api/questMaster';
import { mentorQuestMasterMessages } from '@/constants/questMaster';
import type {
    QuestMasterGroupedData,
    QuestMasterKindFilter,
    QuestMasterQuestRow,
    QuestMasterUnitGroup,
} from '@/types/questMaster';
import type { QuestListMeta } from '@/types/quest';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

const emptyGroupedData = (): QuestMasterGroupedData => ({
    units: [],
    teamQuests: [],
    specialQuests: [],
});

export function useMentorQuestMaster() {
    const grouped = ref<QuestMasterGroupedData>(emptyGroupedData());
    const meta = ref<QuestListMeta | null>(null);
    const kindFilter = ref<QuestMasterKindFilter>('all');
    const searchQuery = ref('');
    const appliedSearch = ref('');
    const isLoading = ref(false);
    const isExporting = ref(false);
    const error = ref('');

    const units = computed((): QuestMasterUnitGroup[] => grouped.value.units);
    const teamQuests = computed((): QuestMasterQuestRow[] => grouped.value.teamQuests);
    const specialQuests = computed((): QuestMasterQuestRow[] => grouped.value.specialQuests);

    const isEmpty = computed((): boolean => {
        if (isLoading.value) {
            return false;
        }

        return (
            units.value.length === 0 &&
            teamQuests.value.length === 0 &&
            specialQuests.value.length === 0
        );
    });

    const load = async (page = 1): Promise<void> => {
        isLoading.value = true;
        error.value = '';

        try {
            const response = await fetchMentorQuestMaster({
                kind: kindFilter.value,
                search: appliedSearch.value,
                page,
            });
            grouped.value = response.data;
            meta.value = response.meta;
        } catch (loadError: unknown) {
            error.value = extractApiErrorMessage(
                loadError,
                undefined,
                mentorQuestMasterMessages.loadFailed,
            );
            grouped.value = emptyGroupedData();
            meta.value = null;
        } finally {
            isLoading.value = false;
        }
    };

    const applyFilters = async (): Promise<void> => {
        appliedSearch.value = searchQuery.value.trim();
        await load(1);
    };

    const changeKindFilter = async (kind: QuestMasterKindFilter): Promise<void> => {
        kindFilter.value = kind;
        await load(1);
    };

    const exportCsv = async (): Promise<boolean> => {
        isExporting.value = true;
        error.value = '';

        try {
            await downloadMentorQuestMasterCsv();
            return true;
        } catch (exportError: unknown) {
            error.value = extractApiErrorMessage(
                exportError,
                undefined,
                mentorQuestMasterMessages.exportFailed,
            );
            return false;
        } finally {
            isExporting.value = false;
        }
    };

    return {
        units,
        teamQuests,
        specialQuests,
        meta,
        kindFilter,
        searchQuery,
        appliedSearch,
        isLoading,
        isExporting,
        error,
        isEmpty,
        load,
        applyFilters,
        changeKindFilter,
        exportCsv,
    };
}
