<template>
    <MentorPanel :config="pageConfig">
        <PageLoadGate
            :is-loading="isLoading"
            :load-error="loadError"
            :loading-message="mentorQuestMasterDetailPageConfig.loadingLabel"
            @retry="loadQuest"
        >
            <div v-if="quest" class="quest-sheet-page mentor-quest-create-page">
                <QuestSheetBackNav
                    :back-to="{ name: 'mentor-quest-master' }"
                    :back-label="mentorQuestMasterDetailPageConfig.backLabel"
                />

                <div class="mentor-quest-create-toolbar">
                    <MentorQuestCreateTypeField
                        :model-value="questCreateType"
                        disabled
                        id="quest-detail-type"
                    />
                </div>

                <div
                    class="mentor-quest-create-sheet-form"
                    :class="{ 'mentor-quest-create-child-quest': quest.type === 'personal' }"
                >
                    <h3
                        v-if="quest.type === 'personal'"
                        class="mentor-quest-create-section-title"
                    >
                        {{ mentorQuestCreatePageConfig.childQuestSectionTitle }}
                    </h3>

                    <QuestSheetLayout :quest-no="quest.sortOrder">
                        <template #title>
                            <input
                                :id="`quest-detail-title-${quest.id}`"
                                v-model="displayForm.title"
                                class="quest-sheet-create-meta-input quest-sheet-heading-title-input"
                                type="text"
                                readonly
                            />
                        </template>

                        <template #quest-status>
                            <span class="quest-sheet-create-type-badge">
                                {{
                                    quest.type === 'personal'
                                        ? mentorPersonalAssignmentSectionConfig.childQuestTypeLabel
                                        : typeLabel
                                }}
                            </span>
                        </template>

                        <template #meta>
                            <QuestSheetMetaSidebar :rows="metaRows" />
                        </template>

                        <QuestSheetCreateSections v-model="sectionForm" disabled />
                    </QuestSheetLayout>
                </div>
            </div>
        </PageLoadGate>
    </MentorPanel>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { fetchMentorQuestDetail } from '@/api/questAdmin';
import {
    mentorPersonalAssignmentSectionConfig,
    mentorQuestCreatePageConfig,
    mentorQuestCreateTypeOptions,
} from '@/constants/questAdmin';
import { mentorQuestMasterDetailPageConfig } from '@/constants/questMaster';
import type { MentorQuestCreateType, MentorQuestDetail } from '@/types/questAdmin';
import { useRouteResourceLoader } from '@/composables/useRouteResourceLoader';
import MentorQuestCreateTypeField from '@/components/rpg/MentorQuestCreateTypeField.vue';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/QuestSheetBackNav.vue';
import QuestSheetCreateSections from '@/components/rpg/QuestSheetCreateSections.vue';
import QuestSheetLayout from '@/components/rpg/QuestSheetLayout.vue';
import QuestSheetMetaSidebar from '@/components/rpg/QuestSheetMetaSidebar.vue';
import {
    createEmptyMentorQuestDisplayForm,
    mapQuestDetailToDisplayForms,
} from '@/utils/mentorQuestFormMapper';
import { createEmptyQuestDescriptionSections } from '@/utils/questDescriptionSections';
import { buildQuestMetaRows } from '@/utils/questSheetMeta';
import { mentorQuestMasterPanelConfig } from '@/utils/mentorQuestMasterPanelConfig';

const route = useRoute();
const questId = computed(() => Number(route.params.questId));

const pageConfig = mentorQuestMasterPanelConfig('詳細', 'fa-solid fa-book-open');

const quest = ref<MentorQuestDetail | null>(null);
const displayForm = reactive(createEmptyMentorQuestDisplayForm());
const sectionForm = reactive(createEmptyQuestDescriptionSections());

const questCreateType = computed((): MentorQuestCreateType => {
    if (!quest.value) {
        return 'team';
    }

    if (quest.value.type === 'personal') {
        return 'personal';
    }

    if (quest.value.type === 'team') {
        return 'team';
    }

    return 'special';
});

const typeLabel = computed((): string => {
    const option = mentorQuestCreateTypeOptions.find(
        (item) => item.value === questCreateType.value,
    );

    return option?.label ?? questCreateType.value;
});

const metaRows = computed(() => {
    if (!quest.value) {
        return [];
    }

    return buildQuestMetaRows({
        tool: quest.value.tool,
        difficulty: quest.value.difficulty,
        experiencePoints: quest.value.experiencePoints,
        questTier: quest.value.questTier,
        unlockLevel: quest.value.unlockLevel,
        skillGrants: quest.value.skillGrants,
        badgeLabel: quest.value.badgeLabel,
    });
});

const applyQuestToForm = (detail: MentorQuestDetail): void => {
    const mapped = mapQuestDetailToDisplayForms(detail);
    Object.assign(displayForm, mapped.displayForm);
    Object.assign(sectionForm, mapped.sectionForm);
};

const { isLoading, loadError, load: loadQuest } = useRouteResourceLoader(questId, {
    loadFailedMessage: mentorQuestMasterDetailPageConfig.loadFailed,
    fetch: async (id) => {
        const detail = await fetchMentorQuestDetail(id);
        quest.value = detail;
        applyQuestToForm(detail);

        return detail;
    },
});
</script>
