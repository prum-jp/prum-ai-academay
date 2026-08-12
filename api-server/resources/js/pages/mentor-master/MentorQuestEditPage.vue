<template>
    <MentorQuestMasterPageShell
        :page-config="pageConfig"
        :is-loading="isLoading"
        :load-error="loadError"
        :loading-message="mentorQuestMasterEditPageConfig.submittingLabel"
        :back-label="mentorQuestMasterEditPageConfig.backLabel"
        page-class="quest-sheet-page mentor-quest-edit-page"
        :ready="quest !== null"
        @retry="loadQuest"
    >
        <template v-if="quest" #back-nav-secondary>
            <RouterLink
                class="mentor-register-link"
                :to="mentorQuestMasterQuestDetailRoute(quest.id)"
            >
                {{ mentorQuestMasterEditPageConfig.detailLabel }}
            </RouterLink>
        </template>

        <form
            v-if="quest?.type === 'personal'"
            class="mentor-quest-create-sheet-form"
            @submit.prevent="onSubmitPersonal"
        >
            <QuestSheetPersonalQuestForm
                v-model:title="personalTitle"
                v-model:sections="personalSections"
                v-model:tool-ids="personalToolIds"
                v-model:difficulty="personalDifficulty"
                v-model:quest-tier="personalQuestTier"
                v-model:skill-grants="personalSkillGrants"
                :quest-no="quest.sortOrder"
                :tools="tools"
                :disabled="isSubmitting"
                editable-meta
                title-id="mentor-quest-edit-title"
                title-required
            />

            <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

            <MentorFormSubmitBar
                :is-submitting="isSubmitting"
                :submit-label="mentorQuestMasterEditPageConfig.submitLabel"
                :submitting-label="mentorQuestMasterEditPageConfig.submittingLabel"
            />
        </form>

        <form v-else-if="quest" class="mentor-quest-create-form" @submit.prevent="onSubmitBoard">
            <MentorQuestFormFields
                :form="questForm"
                :field-errors="fieldErrors"
                id-prefix="mentor-quest-edit"
                :disabled="isSubmittingFromComposable"
            />

            <p v-if="boardErrorMessage" class="login-error">{{ boardErrorMessage }}</p>

            <MentorFormSubmitBar
                :is-submitting="isSubmittingFromComposable"
                :submit-label="mentorQuestMasterEditPageConfig.submitLabel"
                :submitting-label="mentorQuestMasterEditPageConfig.submittingLabel"
            />
        </form>
    </MentorQuestMasterPageShell>
</template>

<script setup lang="ts">
import { computed, reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { fetchMentorQuestDetail, updateMentorPersonalQuest } from '@/api/mentor-quest/questAdmin';
import { fetchMentorTools } from '@/api/mentor-tools/toolAdmin';
import { mentorQuestMasterEditPageConfig } from '@/constants/mentor-master/questMaster';
import type { MentorQuestDetail, MentorTool } from '@/types/mentor-quest/questAdmin';
import type { SkillKey } from '@/constants/shared/skills';
import { createEmptySkillGrants } from '@/constants/shared/skills';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/quest/questTier';
import { useMentorQuestEdit } from '@/composables/mentor-quest/useMentorQuestEdit';
import { useRouteResourceLoader } from '@/composables/shared/useRouteResourceLoader';
import MentorFormSubmitBar from '@/components/rpg/mentor-quest/MentorFormSubmitBar.vue';
import MentorQuestFormFields from '@/components/rpg/mentor-quest/MentorQuestFormFields.vue';
import MentorQuestMasterPageShell from '@/components/rpg/mentor-master/MentorQuestMasterPageShell.vue';
import QuestSheetPersonalQuestForm from '@/components/rpg/quest-sheet/QuestSheetPersonalQuestForm.vue';
import {
    createEmptyQuestDescriptionSections,
    parseQuestDescriptionSections,
    serializeQuestDescriptionSections,
} from '@/utils/quest-sheet/questDescriptionSections';
import { mentorQuestMasterQuestDetailRoute } from '@/utils/mentor-master/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentor-master/mentorQuestMasterPanelConfig';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';

const route = useRoute();
const router = useRouter();
const questId = computed(() => Number(route.params.questId));

const pageConfig = mentorQuestMasterPanelConfig('編集', 'fa-solid fa-pen');

const isSubmitting = ref(false);
const errorMessage = ref('');
const tools = ref<MentorTool[]>([]);

const personalTitle = ref('');
const personalToolIds = ref<number[]>([]);
const personalDifficulty = ref<number | null>(null);
const personalQuestTier = ref<QuestTier>(DEFAULT_QUEST_TIER);
const personalSkillGrants = ref<SkillKey[]>(createEmptySkillGrants());
const personalSections = reactive(createEmptyQuestDescriptionSections());

const {
    questForm,
    fieldErrors,
    errorMessage: boardErrorMessage,
    isSubmitting: isSubmittingFromComposable,
    initQuest,
    submitQuest,
} = useMentorQuestEdit();

const applyPersonalQuest = (detail: MentorQuestDetail): void => {
    personalTitle.value = detail.title;
    personalToolIds.value = detail.toolIds ?? (detail.toolId !== null ? [detail.toolId] : []);
    personalDifficulty.value = detail.difficulty;
    personalQuestTier.value = detail.questTier ?? DEFAULT_QUEST_TIER;
    personalSkillGrants.value = detail.skillGrants ?? createEmptySkillGrants();
    Object.assign(
        personalSections,
        parseQuestDescriptionSections(detail.description, detail.clearCondition),
    );
};

const applyBoardQuest = (detail: MentorQuestDetail): void => {
    initQuest({
        id: detail.id,
        title: detail.title,
        description: detail.description,
        type: detail.type === 'team' ? 'team' : 'special',
        isRequired: detail.isRequired,
        unlockLevel: detail.unlockLevel,
        rewardText: detail.rewardText,
        skillGrants: detail.skillGrants ?? createEmptySkillGrants(),
        badgeLabel: detail.badgeLabel,
        difficulty: detail.difficulty,
        experiencePoints: detail.experiencePoints ?? 0,
        clearCondition: detail.clearCondition,
        sortOrder: detail.sortOrder,
        startsAt: null,
        endsAt: null,
        participantCount: 0,
        isPublished: detail.isPublished,
    });
};

const {
    data: quest,
    isLoading,
    loadError,
    load: loadQuest,
} = useRouteResourceLoader(questId, {
    loadFailedMessage: mentorQuestMasterEditPageConfig.loadFailed,
    fetch: async (id) => {
        const detail = await fetchMentorQuestDetail(id);

        if (detail.type === 'personal') {
            tools.value = await fetchMentorTools();
            applyPersonalQuest(detail);
        } else {
            applyBoardQuest(detail);
        }

        return detail;
    },
});

const onSubmitPersonal = async (): Promise<void> => {
    if (!quest.value || isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    const serialized = serializeQuestDescriptionSections(personalSections);

    try {
        await updateMentorPersonalQuest(quest.value.id, {
            title: personalTitle.value.trim(),
            description: serialized.description,
            clearCondition: serialized.clearCondition,
            toolIds: [...personalToolIds.value],
            difficulty: personalDifficulty.value,
            questTier: personalQuestTier.value,
            skillGrants: [...personalSkillGrants.value],
        });

        await router.push(mentorQuestMasterQuestDetailRoute(quest.value.id));
    } catch (error: unknown) {
        errorMessage.value = extractApiErrorMessage(
            error,
            undefined,
            mentorQuestMasterEditPageConfig.updateFailed,
        );
    } finally {
        isSubmitting.value = false;
    }
};

const onSubmitBoard = async (): Promise<void> => {
    if (!quest.value) {
        return;
    }

    const success = await submitQuest();
    if (!success) {
        return;
    }

    await router.push(mentorQuestMasterQuestDetailRoute(quest.value.id));
};
</script>
