<template>
    <MentorQuestMasterPageShell
        :page-config="pageConfig"
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="mentorQuestMasterEditPageConfig.submittingLabel"
        :back-label="mentorQuestMasterEditPageConfig.backLabel"
        bare
        @retry="loadUnit"
    >
        <template v-if="unitId" #back-nav-secondary>
            <RouterLink
                class="mentor-register-link"
                :to="mentorQuestMasterUnitDetailRoute(unitId)"
            >
                {{ mentorQuestMasterEditPageConfig.detailLabel }}
            </RouterLink>
        </template>

        <form class="mentor-quest-create-form" @submit.prevent="onSubmit">
            <section>
                <MentorUnitFormFields
                    :form="unitForm"
                    :field-errors="fieldErrors"
                    id-prefix="edit-unit"
                    :disabled="isSubmitting"
                />
            </section>

            <MentorUnitChildQuestEditor
                :key="unitId"
                :quests="unitForm.quests"
                :tools="tools"
                :disabled="isSubmitting"
                @add="addChildQuest"
                @remove="removeChildQuest"
            />

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

            <MentorFormSubmitBar
                :is-submitting="isSubmitting"
                :submit-label="mentorQuestMasterEditPageConfig.submitLabel"
                :submitting-label="mentorQuestMasterEditPageConfig.submittingLabel"
            />
        </form>
    </MentorQuestMasterPageShell>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { mentorQuestMasterEditPageConfig } from '@/constants/mentor-master/questMaster';
import { useMentorQuestEdit } from '@/composables/mentor-quest/useMentorQuestEdit';
import { useRouteResourceLoader } from '@/composables/shared/useRouteResourceLoader';
import MentorFormSubmitBar from '@/components/rpg/mentor-quest/MentorFormSubmitBar.vue';
import MentorQuestMasterPageShell from '@/components/rpg/mentor-master/MentorQuestMasterPageShell.vue';
import MentorUnitChildQuestEditor from '@/components/rpg/mentor-master/MentorUnitChildQuestEditor.vue';
import MentorUnitFormFields from '@/components/rpg/mentor-quest/MentorUnitFormFields.vue';
import { mentorQuestMasterUnitDetailRoute } from '@/utils/mentor-master/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentor-master/mentorQuestMasterPanelConfig';

const route = useRoute();
const router = useRouter();
const unitId = computed(() => Number(route.params.unitId));

const pageConfig = mentorQuestMasterPanelConfig('ユニット編集', 'fa-solid fa-pen');

const {
    tools,
    unitForm,
    isSubmitting,
    errorMessage,
    fieldErrors,
    loadUnitDetail,
    addChildQuest,
    removeChildQuest,
    submitUnit,
} = useMentorQuestEdit();

const { isLoading, loadError, load: loadUnit } = useRouteResourceLoader(unitId, {
    loadFailedMessage: mentorQuestMasterEditPageConfig.loadFailed,
    fetch: async (id) => {
        await loadUnitDetail(id);
        return true;
    },
});

const onSubmit = async (): Promise<void> => {
    const success = await submitUnit();
    if (!success) {
        return;
    }

    await router.push(mentorQuestMasterUnitDetailRoute(unitId.value));
};
</script>
