<template>
    <MentorPanel :config="pageConfig">
        <QuestSheetBackNav
            :back-to="{ name: 'mentor-quest-master' }"
            :back-label="mentorQuestMasterEditPageConfig.backLabel"
        >
            <template v-if="unitId" #secondary>
                <RouterLink
                    class="mentor-register-link"
                    :to="mentorQuestMasterUnitDetailRoute(unitId)"
                >
                    {{ mentorQuestMasterEditPageConfig.detailLabel }}
                </RouterLink>
            </template>
        </QuestSheetBackNav>

        <PageLoadGate
            :is-loading="isLoading"
            :load-error="loadError"
            :loading-message="mentorQuestMasterEditPageConfig.submittingLabel"
            @retry="loadUnit"
        >
            <form class="mentor-quest-create-form" @submit.prevent="onSubmit">
                <section>
                    <MentorUnitFormFields
                        :form="unitForm"
                        :field-errors="fieldErrors"
                        id-prefix="edit-unit"
                        :disabled="isSubmitting"
                    />
                </section>

                <MentorChildQuestFields
                    :quests="unitForm.quests"
                    :tools="tools"
                    :disabled="isSubmitting"
                    @add="addChildQuest"
                    @remove="removeChildQuest"
                />

                <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

                <div class="mentor-quest-create-page-actions">
                    <RpgButton type="submit" icon="fa-solid fa-floppy-disk" :disabled="isSubmitting">
                        {{
                            isSubmitting
                                ? mentorQuestMasterEditPageConfig.submittingLabel
                                : mentorQuestMasterEditPageConfig.submitLabel
                        }}
                    </RpgButton>
                </div>
            </form>
        </PageLoadGate>
    </MentorPanel>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { mentorQuestMasterEditPageConfig } from '@/constants/questMaster';
import { useMentorQuestEdit } from '@/composables/useMentorQuestEdit';
import MentorChildQuestFields from '@/components/rpg/MentorChildQuestFields.vue';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorUnitFormFields from '@/components/rpg/MentorUnitFormFields.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/QuestSheetBackNav.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import { mentorQuestMasterUnitDetailRoute } from '@/utils/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentorQuestMasterPanelConfig';

const route = useRoute();
const router = useRouter();
const unitId = computed(() => Number(route.params.unitId));

const pageConfig = mentorQuestMasterPanelConfig('ユニット編集', 'fa-solid fa-pen');

const loadError = ref('');

const {
    tools,
    unitForm,
    isLoading,
    isSubmitting,
    errorMessage,
    fieldErrors,
    initUnit,
    addChildQuest,
    removeChildQuest,
    submitUnit,
} = useMentorQuestEdit();

const loadUnit = async (): Promise<void> => {
    if (!Number.isFinite(unitId.value) || unitId.value <= 0) {
        loadError.value = mentorQuestMasterEditPageConfig.loadFailed;
        isLoading.value = false;
        return;
    }

    loadError.value = '';

    try {
        await initUnit({
            id: unitId.value,
            title: '',
            description: '',
            sortOrder: 0,
            questCount: 0,
            isPublished: false,
        });

        if (errorMessage.value) {
            loadError.value = mentorQuestMasterEditPageConfig.loadFailed;
        }
    } catch {
        loadError.value = mentorQuestMasterEditPageConfig.loadFailed;
    }
};

const onSubmit = async (): Promise<void> => {
    const success = await submitUnit();
    if (!success) {
        return;
    }

    await router.push(mentorQuestMasterUnitDetailRoute(unitId.value));
};

onMounted(() => {
    void loadUnit();
});
</script>
