<template>
    <MentorPanel :config="pageConfig">
        <PageLoadGate
            :is-loading="isLoading"
            :load-error="loadError"
            :loading-message="mentorQuestMasterDetailPageConfig.loadingLabel"
            @retry="loadUnit"
        >
            <div v-if="unit" class="quest-sheet-page mentor-quest-create-page">
                <QuestSheetBackNav
                    :back-to="{ name: 'mentor-quest-master' }"
                    :back-label="mentorQuestMasterDetailPageConfig.backLabel"
                />

                <div class="mentor-quest-create-toolbar">
                    <MentorQuestCreateTypeField
                        model-value="personal"
                        disabled
                        id="unit-detail-type"
                    />
                </div>

                <div class="mentor-quest-create-form">
                    <section class="mentor-quest-create-unit-form">
                        <MentorUnitFormFields
                            :form="unitForm"
                            :field-errors="{}"
                            id-prefix="unit-detail"
                            disabled
                            readonly
                        />
                    </section>

                    <section
                        v-if="unit.quests.length > 0"
                        class="mentor-quest-create-added-list"
                    >
                        <h3 class="mentor-quest-create-section-title">
                            {{ mentorQuestCreatePageConfig.addedChildQuestsTitle }}
                            ({{ unit.quests.length }})
                        </h3>
                        <ul class="mentor-quest-create-added-items">
                            <li
                                v-for="childQuest in unit.quests"
                                :key="childQuest.id"
                                class="mentor-quest-create-added-item"
                            >
                                <span class="mentor-quest-create-added-index">
                                    {{ childQuest.sortOrder }}
                                </span>
                                <RouterLink
                                    class="mentor-quest-create-added-title mentor-quest-detail-child-link"
                                    :to="mentorQuestMasterQuestDetailRoute(childQuest.id)"
                                >
                                    {{ childQuest.title }}
                                </RouterLink>
                            </li>
                        </ul>
                    </section>
                </div>
            </div>
        </PageLoadGate>
    </MentorPanel>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { fetchMentorQuestUnitDetail } from '@/api/questAdmin';
import { mentorQuestCreatePageConfig } from '@/constants/questAdmin';
import { mentorQuestMasterDetailPageConfig } from '@/constants/questMaster';
import type { MentorQuestUnitDetail } from '@/types/questAdmin';
import { useRouteResourceLoader } from '@/composables/useRouteResourceLoader';
import MentorQuestCreateTypeField from '@/components/rpg/MentorQuestCreateTypeField.vue';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorUnitFormFields from '@/components/rpg/MentorUnitFormFields.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/QuestSheetBackNav.vue';
import { mentorQuestMasterQuestDetailRoute } from '@/utils/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentorQuestMasterPanelConfig';

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

<style scoped>
.mentor-quest-detail-child-link {
    color: inherit;
    text-decoration: none;
}

.mentor-quest-detail-child-link:hover {
    color: var(--orange-main);
    text-decoration: underline;
}
</style>
