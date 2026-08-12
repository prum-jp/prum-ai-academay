<template>
    <MentorQuestMasterPageShell
        :page-config="pageConfig"
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="mentorQuestMasterDetailPageConfig.loadingLabel"
        :back-label="mentorQuestMasterDetailPageConfig.backLabel"
        :ready="quest !== null"
        @retry="loadQuest"
    >
        <template #toolbar>
            <div class="mentor-quest-create-toolbar">
                <MentorQuestCreateTypeField
                    :model-value="questCreateType"
                    disabled
                    id="quest-detail-type"
                />
            </div>
        </template>

        <QuestSheetPersonalQuestForm
            v-if="quest"
            v-model:title="displayForm.title"
            v-model:sections="sectionForm"
            :quest-no="quest.sortOrder"
            :section-title="
                quest.type === 'personal'
                    ? mentorQuestCreatePageConfig.childQuestSectionTitle
                    : ''
            "
            :status-label="
                quest.type === 'personal'
                    ? mentorPersonalAssignmentSectionConfig.childQuestTypeLabel
                    : typeLabel
            "
            :child-quest-style="quest.type === 'personal'"
            :disabled="true"
            title-readonly
            :title-id="`quest-detail-title-${quest.id}`"
        >
            <template #meta>
                <QuestSheetMetaSidebar :rows="metaRows" />
            </template>
        </QuestSheetPersonalQuestForm>
    </MentorQuestMasterPageShell>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { fetchMentorQuestDetail } from '@/api/mentor-quest/questAdmin';
import {
    mentorPersonalAssignmentSectionConfig,
    mentorQuestCreatePageConfig,
    mentorQuestCreateTypeOptions,
} from '@/constants/mentor-quest/questAdmin';
import { mentorQuestMasterDetailPageConfig } from '@/constants/mentor-master/questMaster';
import type { MentorQuestCreateType, MentorQuestDetail } from '@/types/mentor-quest/questAdmin';
import { useRouteResourceLoader } from '@/composables/shared/useRouteResourceLoader';
import MentorQuestCreateTypeField from '@/components/rpg/mentor-quest/MentorQuestCreateTypeField.vue';
import MentorQuestMasterPageShell from '@/components/rpg/mentor-master/MentorQuestMasterPageShell.vue';
import QuestSheetMetaSidebar from '@/components/rpg/quest-sheet/QuestSheetMetaSidebar.vue';
import QuestSheetPersonalQuestForm from '@/components/rpg/quest-sheet/QuestSheetPersonalQuestForm.vue';
import {
    createEmptyMentorQuestDisplayForm,
    mapQuestDetailToDisplayForms,
} from '@/utils/mentor-quest/mentorQuestFormMapper';
import { createEmptyQuestDescriptionSections } from '@/utils/quest-sheet/questDescriptionSections';
import { buildQuestMetaRows } from '@/utils/quest-sheet/questSheetMeta';
import { mentorQuestMasterPanelConfig } from '@/utils/mentor-master/mentorQuestMasterPanelConfig';

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
