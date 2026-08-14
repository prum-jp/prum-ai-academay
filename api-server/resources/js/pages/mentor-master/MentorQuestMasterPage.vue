<template>
    <MentorPanel :config="mentorQuestMasterPageConfig">
        <RpgCard
            :title="mentorQuestMasterPageConfig.title.replace('管理 / ', '')"
            :icon="mentorQuestMasterPageConfig.icon"
        >
            <template #title-extra>
                <RouterLink class="mentor-register-link" :to="{ name: 'mentor-quests' }">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                    {{ mentorQuestMasterPageConfig.operationsLinkLabel }}
                </RouterLink>
            </template>

            <p class="mentor-message">{{ mentorQuestMasterPageConfig.description }}</p>

            <div class="mentor-quest-master-toolbar">
                <div class="mentor-quest-master-filters">
                    <div class="input-group mentor-quest-master-filter">
                        <label for="quest-master-kind">{{ mentorQuestMasterPageConfig.kindFilterLabel }}</label>
                        <select
                            id="quest-master-kind"
                            :value="kindFilter"
                            :disabled="isLoading || isExporting"
                            @change="onKindChange"
                        >
                            <option
                                v-for="option in mentorQuestMasterKindOptions"
                                :key="option.value"
                                :value="option.value"
                            >
                                {{ option.label }}
                            </option>
                        </select>
                    </div>

                    <form class="mentor-student-search mentor-quest-master-search" @submit.prevent="applyFilters">
                        <div class="mentor-student-search-row">
                            <div class="mentor-student-search-field">
                                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                                <input
                                    id="quest-master-search"
                                    v-model="searchQuery"
                                    type="search"
                                    :placeholder="mentorQuestMasterPageConfig.searchPlaceholder"
                                    :disabled="isLoading || isExporting"
                                />
                            </div>
                            <button
                                type="submit"
                                class="mentor-student-search-btn"
                                :disabled="isLoading || isExporting"
                            >
                                {{ mentorQuestMasterPageConfig.searchLabel }}
                            </button>
                        </div>
                    </form>
                </div>

                <div class="mentor-quest-master-actions">
                    <button
                        type="button"
                        class="mentor-register-link"
                        :disabled="isLoading || isExporting"
                        @click="onExportCsv"
                    >
                        <i class="fa-solid fa-file-arrow-down" aria-hidden="true"></i>
                        {{ mentorQuestMasterPageConfig.exportCsvLabel }}
                    </button>
                    <button
                        type="button"
                        class="mentor-register-link"
                        :disabled="isLoading || isExporting"
                        @click="isBulkImportOpen = true"
                    >
                        <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                        {{ mentorQuestMasterPageConfig.csvUpdateLabel }}
                    </button>
                    <RouterLink class="mentor-register-link" :to="{ name: 'mentor-quest-create' }">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i>
                        {{ mentorQuestMasterPageConfig.createLabel }}
                    </RouterLink>
                </div>
            </div>

            <p class="mentor-register-note">{{ mentorQuestMasterPageConfig.exportCsvHint }}</p>

            <AsyncState
                :is-loading="isLoading"
                :error="error"
                :is-empty="isEmpty"
                :loading-message="mentorQuestMasterPageConfig.loadingLabel"
                :empty-message="mentorQuestMasterPageConfig.emptyLabel"
            >
                <div class="mentor-quest-master-groups">
                    <section
                        v-for="unit in units"
                        :key="unit.id"
                        class="mentor-quest-master-unit"
                    >
                        <header class="mentor-quest-master-unit-header">
                            <div class="mentor-quest-master-unit-title">
                                <span class="mentor-quest-master-unit-no">
                                    {{ mentorQuestMasterPageConfig.unitSortLabel(unit.sortOrder) }}
                                </span>
                                <h3>{{ unit.title }}</h3>
                            </div>
                            <div class="mentor-quest-master-unit-meta">
                                <span class="mentor-quest-master-unit-count">
                                    {{ mentorQuestMasterPageConfig.unitQuestCountLabel(unit.questCount) }}
                                </span>
                                <MentorRowActionMenu
                                    :detail-to="mentorQuestMasterUnitDetailRoute(unit.id)"
                                    :edit-to="mentorQuestMasterUnitEditRoute(unit.id)"
                                    @delete="onDeleteUnit(unit)"
                                />
                            </div>
                        </header>

                        <div
                            v-if="unit.quests.length === 0"
                            class="mentor-quest-master-empty-unit"
                        >
                            {{ mentorQuestMasterPageConfig.emptyUnitQuests }}
                        </div>

                        <MentorQuestMasterQuestTable
                            v-else
                            :quests="unit.quests"
                            @delete="onDeleteQuest"
                        />
                    </section>

                    <section
                        v-if="teamQuests.length > 0"
                        class="mentor-quest-master-unit mentor-quest-master-board-section"
                    >
                        <header class="mentor-quest-master-unit-header">
                            <h3>{{ mentorQuestMasterSectionLabels.team }}</h3>
                        </header>
                        <MentorQuestMasterQuestTable :quests="teamQuests" @delete="onDeleteQuest" />
                    </section>

                    <section
                        v-if="specialQuests.length > 0"
                        class="mentor-quest-master-unit mentor-quest-master-board-section"
                    >
                        <header class="mentor-quest-master-unit-header">
                            <h3>{{ mentorQuestMasterSectionLabels.special }}</h3>
                        </header>
                        <MentorQuestMasterQuestTable :quests="specialQuests" @delete="onDeleteQuest" />
                    </section>
                </div>
            </AsyncState>

            <QuestPagination
                v-if="units.length > 0 || (meta?.total ?? 0) > 0"
                :meta="meta"
                :disabled="isLoading || isExporting"
                @page-change="load"
            />
        </RpgCard>

        <MentorQuestBulkImportModal
            :open="isBulkImportOpen"
            @close="isBulkImportOpen = false"
            @imported="onImported"
        />

        <MentorQuestMasterDeleteModal
            :open="isDeleteModalOpen"
            :target="deleteTarget"
            :impact="impact"
            :is-loading-impact="isLoadingImpact"
            :is-deleting="isDeleting"
            :error-message="deleteErrorMessage"
            @close="closeDeleteModal"
            @confirm="onConfirmDelete"
        />

        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import {
    mentorQuestMasterKindOptions,
    mentorQuestMasterMessages,
    mentorQuestMasterPageConfig,
    mentorQuestMasterSectionLabels,
    mentorQuestMasterDeleteModalConfig,
} from '@/constants/mentor-master/questMaster';
import type {
    QuestMasterKindFilter,
    QuestMasterQuestRow,
    QuestMasterUnitGroup,
} from '@/types/mentor-master/questMaster';
import { useMentorQuestMaster } from '@/composables/mentor-master/useMentorQuestMaster';
import { useMentorQuestMasterDelete } from '@/composables/mentor-master/useMentorQuestMasterDelete';
import { useToastNotice } from '@/composables/shared/useToastNotice';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import MentorQuestBulkImportModal from '@/components/rpg/mentor-quest/MentorQuestBulkImportModal.vue';
import MentorQuestMasterDeleteModal from '@/components/rpg/mentor-master/MentorQuestMasterDeleteModal.vue';
import MentorQuestMasterQuestTable from '@/components/rpg/mentor-master/MentorQuestMasterQuestTable.vue';
import MentorRowActionMenu from '@/components/rpg/mentor-master/MentorRowActionMenu.vue';
import QuestPagination from '@/components/rpg/shared/QuestPagination.vue';
import {
    mentorQuestMasterUnitDetailRoute,
    mentorQuestMasterUnitEditRoute,
} from '@/utils/mentor-master/mentorQuestMasterRoutes';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';
import ToastNotice from '@/components/rpg/shared/ToastNotice.vue';

const isBulkImportOpen = ref(false);
const { showToast, toastMessage, showNotice } = useToastNotice();
const {
    deleteTarget,
    isDeleteModalOpen,
    impact,
    isLoadingImpact,
    isDeleting,
    errorMessage: deleteErrorMessage,
    openDeleteModal,
    closeDeleteModal,
    confirmDelete,
} = useMentorQuestMasterDelete();

const {
    units,
    teamQuests,
    specialQuests,
    meta,
    kindFilter,
    searchQuery,
    isLoading,
    isExporting,
    error,
    isEmpty,
    load,
    applyFilters,
    changeKindFilter,
    exportCsv,
} = useMentorQuestMaster();

const onKindChange = async (event: Event): Promise<void> => {
    const value = (event.target as HTMLSelectElement).value as QuestMasterKindFilter;
    await changeKindFilter(value);
};

const onExportCsv = async (): Promise<void> => {
    const success = await exportCsv();
    if (success) {
        showNotice(mentorQuestMasterMessages.exportSuccess);
    }
};

const onImported = async (): Promise<void> => {
    isBulkImportOpen.value = false;
    await load(meta.value?.currentPage ?? 1);
    showNotice(mentorQuestMasterMessages.imported);
};

const onDeleteUnit = (unit: QuestMasterUnitGroup): void => {
    void openDeleteModal({
        kind: 'personal_unit',
        id: unit.id,
        title: unit.title,
    });
};

const onDeleteQuest = (quest: QuestMasterQuestRow): void => {
    void openDeleteModal({
        kind: quest.kind,
        id: quest.id,
        title: quest.title,
    });
};

const onConfirmDelete = async (): Promise<void> => {
    const success = await confirmDelete();
    if (!success) {
        return;
    }

    await load(meta.value?.currentPage ?? 1);
    showNotice(mentorQuestMasterDeleteModalConfig.deleteSuccess);
};

onMounted(() => {
    void load();
});
</script>

<style scoped>
.mentor-quest-master-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 12px;
}

.mentor-quest-master-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-end;
    flex: 1 1 320px;
}

.mentor-quest-master-filter {
    min-width: 160px;
}

.mentor-quest-master-search {
    flex: 1 1 280px;
    margin-bottom: 0;
}

.mentor-quest-master-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}

.mentor-quest-master-groups {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.mentor-quest-master-unit {
    border: 2px solid #111;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}

.mentor-quest-master-unit-header {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    justify-content: space-between;
    align-items: center;
    padding: 14px 16px;
    background: #fff3e6;
    border-bottom: 2px solid #111;
}

.mentor-quest-master-unit-title {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.mentor-quest-master-unit-title h3 {
    margin: 0;
    font-size: 16px;
}

.mentor-quest-master-unit-no {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border-radius: 999px;
    background: #111;
    color: #fff;
    font-size: 12px;
    font-weight: 900;
    white-space: nowrap;
}

.mentor-quest-master-unit-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    font-size: 14px;
}

.mentor-quest-master-unit-count {
    color: #555;
    font-weight: 700;
}

.mentor-quest-master-empty-unit {
    padding: 16px;
    color: #666;
}

.mentor-quest-master-board-section .mentor-quest-master-unit-header h3 {
    margin: 0;
    font-size: 16px;
}
</style>
