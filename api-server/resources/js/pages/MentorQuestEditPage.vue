<template>
    <MentorPanel :config="pageConfig">
        <PageLoadGate
            :is-loading="isLoading"
            :load-error="loadError"
            :loading-message="mentorQuestMasterEditPageConfig.submittingLabel"
            @retry="loadQuest"
        >
            <div v-if="quest" class="quest-sheet-page mentor-quest-edit-page">
                <QuestSheetBackNav
                    :back-to="{ name: 'mentor-quest-master' }"
                    :back-label="mentorQuestMasterEditPageConfig.backLabel"
                >
                    <template #secondary>
                        <RouterLink
                            class="mentor-register-link"
                            :to="mentorQuestMasterQuestDetailRoute(quest.id)"
                        >
                            {{ mentorQuestMasterEditPageConfig.detailLabel }}
                        </RouterLink>
                    </template>
                </QuestSheetBackNav>

            <form
                v-if="quest.type === 'personal'"
                class="mentor-quest-create-sheet-form"
                @submit.prevent="onSubmitPersonal"
            >
                <QuestSheetLayout :quest-no="quest.sortOrder">
                    <template #title>
                        <input
                            id="mentor-quest-edit-title"
                            v-model="personalTitle"
                            class="quest-sheet-create-meta-input quest-sheet-heading-title-input"
                            type="text"
                            required
                            maxlength="255"
                            :disabled="isSubmitting"
                        />
                    </template>

                    <template #quest-status>
                        <span class="quest-sheet-create-type-badge">
                            {{ mentorPersonalAssignmentSectionConfig.childQuestTypeLabel }}
                        </span>
                    </template>

                    <template #meta>
                        <QuestSheetCreateChildMetaFields
                            v-model:tool-id="personalToolId"
                            v-model:difficulty="personalDifficulty"
                            v-model:quest-tier="personalQuestTier"
                            v-model:skill-grants="personalSkillGrants"
                            :tools="tools"
                            :disabled="isSubmitting"
                        />
                    </template>

                    <QuestSheetCreateSections v-model="personalSections" :disabled="isSubmitting" />
                </QuestSheetLayout>

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

            <form v-else class="mentor-quest-create-form" @submit.prevent="onSubmitBoard">
                <MentorQuestFormFields
                    :form="questForm"
                    :field-errors="fieldErrors"
                    id-prefix="mentor-quest-edit"
                    :disabled="isSubmittingFromComposable"
                />

                <p v-if="boardErrorMessage" class="login-error">{{ boardErrorMessage }}</p>

                <div class="mentor-quest-create-page-actions">
                    <RpgButton
                        type="submit"
                        icon="fa-solid fa-floppy-disk"
                        :disabled="isSubmittingFromComposable"
                    >
                        {{
                            isSubmittingFromComposable
                                ? mentorQuestMasterEditPageConfig.submittingLabel
                                : mentorQuestMasterEditPageConfig.submitLabel
                        }}
                    </RpgButton>
                </div>
            </form>
            </div>
        </PageLoadGate>
    </MentorPanel>
</template>

<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { fetchMentorQuestDetail, updateMentorPersonalQuest } from '@/api/questAdmin';
import { fetchMentorTools } from '@/api/toolAdmin';
import { mentorPersonalAssignmentSectionConfig } from '@/constants/questAdmin';
import {
    mentorQuestMasterEditPageConfig,
} from '@/constants/questMaster';
import type { MentorQuestDetail, MentorTool } from '@/types/questAdmin';
import type { SkillKey } from '@/constants/skills';
import { createEmptySkillGrants } from '@/constants/skills';
import { DEFAULT_QUEST_TIER, type QuestTier } from '@/constants/questTier';
import { useMentorQuestEdit } from '@/composables/useMentorQuestEdit';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorQuestFormFields from '@/components/rpg/MentorQuestFormFields.vue';
import PageLoadGate from '@/components/rpg/PageLoadGate.vue';
import QuestSheetBackNav from '@/components/rpg/QuestSheetBackNav.vue';
import QuestSheetCreateChildMetaFields from '@/components/rpg/QuestSheetCreateChildMetaFields.vue';
import QuestSheetCreateSections from '@/components/rpg/QuestSheetCreateSections.vue';
import QuestSheetLayout from '@/components/rpg/QuestSheetLayout.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import {
    createEmptyQuestDescriptionSections,
    parseQuestDescriptionSections,
    serializeQuestDescriptionSections,
} from '@/utils/questDescriptionSections';
import { mentorQuestMasterQuestDetailRoute } from '@/utils/mentorQuestMasterRoutes';
import { mentorQuestMasterPanelConfig } from '@/utils/mentorQuestMasterPanelConfig';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';

const route = useRoute();
const router = useRouter();
const questId = computed(() => Number(route.params.questId));

const pageConfig = mentorQuestMasterPanelConfig('編集', 'fa-solid fa-pen');

const quest = ref<MentorQuestDetail | null>(null);
const isLoading = ref(true);
const loadError = ref('');
const isSubmitting = ref(false);
const errorMessage = ref('');
const tools = ref<MentorTool[]>([]);

const personalTitle = ref('');
const personalToolId = ref<number | null>(null);
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

const loadQuest = async (): Promise<void> => {
    if (!Number.isFinite(questId.value) || questId.value <= 0) {
        loadError.value = mentorQuestMasterEditPageConfig.loadFailed;
        isLoading.value = false;
        return;
    }

    isLoading.value = true;
    loadError.value = '';

    try {
        const detail = await fetchMentorQuestDetail(questId.value);
        quest.value = detail;

        if (detail.type === 'personal') {
            tools.value = await fetchMentorTools();
            personalTitle.value = detail.title;
            personalToolId.value = detail.toolId;
            personalDifficulty.value = detail.difficulty;
            personalQuestTier.value = detail.questTier ?? DEFAULT_QUEST_TIER;
            personalSkillGrants.value = detail.skillGrants ?? createEmptySkillGrants();
            Object.assign(
                personalSections,
                parseQuestDescriptionSections(detail.description, detail.clearCondition),
            );
        } else {
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
        }
    } catch {
        quest.value = null;
        loadError.value = mentorQuestMasterEditPageConfig.loadFailed;
    } finally {
        isLoading.value = false;
    }
};

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
            toolId: personalToolId.value,
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

onMounted(() => {
    void loadQuest();
});
</script>
