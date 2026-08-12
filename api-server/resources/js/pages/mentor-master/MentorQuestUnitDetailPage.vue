<template>
    <MentorQuestMasterPageShell
        :page-config="pageConfig"
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="mentorQuestMasterDetailPageConfig.loadingLabel"
        :back-label="mentorQuestMasterDetailPageConfig.backLabel"
        :ready="unit !== null"
        @retry="loadUnit"
    >
        <template #toolbar>
            <div class="mentor-quest-create-toolbar">
                <MentorQuestCreateTypeField
                    model-value="personal"
                    disabled
                    id="unit-detail-type"
                />
            </div>
        </template>

        <div v-if="unit" class="mentor-quest-create-form">
            <section class="mentor-quest-create-unit-form">
                <MentorUnitFormFields
                    :form="unitForm"
                    :field-errors="{}"
                    id-prefix="unit-detail"
                    disabled
                    readonly
                />
            </section>

            <MentorChildQuestList
                :items="childQuestListItems"
                :title="mentorQuestCreatePageConfig.addedChildQuestsTitle"
            />
        </div>
    </MentorQuestMasterPageShell>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import { fetchMentorQuestUnitDetail } from '@/api/mentor-quest/questAdmin';
import { mentorQuestCreatePageConfig } from '@/constants/mentor-quest/questAdmin';
import { mentorQuestMasterDetailPageConfig } from '@/constants/mentor-master/questMaster';
import type { MentorQuestUnitDetail } from '@/types/mentor-quest/questAdmin';
import { useRouteResourceLoader } from '@/composables/shared/useRouteResourceLoader';
import MentorChildQuestList from '@/components/rpg/mentor-quest/MentorChildQuestList.vue';
import MentorQuestCreateTypeField from '@/components/rpg/mentor-quest/MentorQuestCreateTypeField.vue';
import MentorQuestMasterPageShell from '@/components/rpg/mentor-master/MentorQuestMasterPageShell.vue';
import MentorUnitFormFields from '@/components/rpg/mentor-quest/MentorUnitFormFields.vue';
import { mentorQuestMasterQuestDetailRoute } from '@/utils/mentor-master/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentor-master/mentorQuestMasterPanelConfig';

const route = useRoute();
const unitId = computed(() => Number(route.params.unitId));

const pageConfig = mentorQuestMasterPanelConfig('ユニット詳細', 'fa-solid fa-book-open');

const unit = ref<MentorQuestUnitDetail | null>(null);

const unitForm = reactive({
    title: '',
});

const applyUnitToForm = (detail: MentorQuestUnitDetail): void => {
    unitForm.title = detail.title;
};

const childQuestListItems = computed(() =>
    (unit.value?.quests ?? []).map((quest) => ({
        id: quest.id,
        sortOrder: quest.sortOrder,
        title: quest.title,
        linkTo: mentorQuestMasterQuestDetailRoute(quest.id),
    })),
);

const { isLoading, loadError, load: loadUnit } = useRouteResourceLoader(unitId, {
    loadFailedMessage: mentorQuestMasterDetailPageConfig.loadFailed,
    fetch: async (id) => {
        const detail = await fetchMentorQuestUnitDetail(id);
        unit.value = detail;
        applyUnitToForm(detail);

        return detail;
    },
});
</script>
