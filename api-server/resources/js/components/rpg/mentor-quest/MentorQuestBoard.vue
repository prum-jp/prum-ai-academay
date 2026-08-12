<template>
    <RpgCard
        :title="mentorQuestBoardCardConfig.title"
        :icon="mentorQuestBoardCardConfig.icon"
    >
        <template #title-extra>
            <div class="mentor-quest-board-links">
                <RouterLink class="mentor-register-link" :to="{ name: 'mentor-quest-master' }">
                    <i class="fa-solid fa-book" aria-hidden="true"></i>
                    {{ mentorQuestBoardCardConfig.masterLinkLabel }}
                </RouterLink>
                <RouterLink class="mentor-register-link" :to="{ name: 'mentor-register' }">
                    <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                    {{ mentorQuestBoardCardConfig.userRegisterLinkLabel }}
                </RouterLink>
                <RouterLink
                    class="mentor-register-link"
                    :to="{ name: 'mentor-quest-create' }"
                >
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                    {{ mentorQuestBoardCardConfig.createButtonLabel }}
                </RouterLink>
            </div>
        </template>

        <p class="mentor-message">{{ mentorQuestBoardCardConfig.description }}</p>

        <div class="quest-notebook mentor-quest-admin-board">
            <MentorPersonalAssignmentBoard
                :disabled="isDeleting"
                @notify="(message) => emit('notify', message)"
            />

            <MentorQuestAdminSection
                v-for="definition in boardSectionDefinitions"
                :key="definition.type"
                :title="definition.title"
                :icon="definition.icon"
                :meta="sections[definition.type].meta"
                :is-empty="sections[definition.type].quests.length === 0"
                :empty-message="mentorQuestAdminMessages.emptyQuests"
                :is-loading="sections[definition.type].isLoading"
                :error="sections[definition.type].error"
                @page-change="(page) => loadSection(definition.type, page)"
            >
                <MentorQuestAdminItemCard
                    v-for="quest in sections[definition.type].quests"
                    :key="quest.id"
                    :quest="quest"
                    :disabled="isDeleting"
                    @delete="onDeleteQuest(quest)"
                    @toggle-publish="onToggleQuestPublish(definition.type, quest)"
                />
            </MentorQuestAdminSection>
        </div>
    </RpgCard>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import {
    mentorQuestAdminMessages,
    mentorQuestAdminSectionDefinitions,
    mentorQuestBoardCardConfig,
    mentorQuestPublishMessages,
    type NonPersonalQuestType,
} from '@/constants/mentor-quest/questAdmin';
import type { MentorQuestItem } from '@/types/mentor-quest/questAdmin';
import { useMentorQuestCatalog } from '@/composables/mentor-quest/useMentorQuestCatalog';
import { useMentorQuestDelete } from '@/composables/mentor-quest/useMentorQuestEdit';
import MentorPersonalAssignmentBoard from '@/components/rpg/mentor-quest/MentorPersonalAssignmentBoard.vue';
import MentorQuestAdminItemCard from '@/components/rpg/mentor-quest/MentorQuestAdminItemCard.vue';
import MentorQuestAdminSection from '@/components/rpg/mentor-quest/MentorQuestAdminSection.vue';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';

const emit = defineEmits<{
    notify: [message: string];
}>();

const boardSectionDefinitions = mentorQuestAdminSectionDefinitions.filter(
    (definition): definition is typeof definition & { type: NonPersonalQuestType } =>
        definition.type !== 'personal',
);

const { sections, loadSection, setQuestPublished, reloadTeamSections } = useMentorQuestCatalog();
const { isDeleting, removeQuest } = useMentorQuestDelete();

const onToggleQuestPublish = async (
    type: NonPersonalQuestType,
    quest: MentorQuestItem,
): Promise<void> => {
    const next = !quest.isPublished;
    const success = await setQuestPublished(type, quest, next);
    if (!success) {
        emit('notify', mentorQuestPublishMessages.publishFailed);
        return;
    }

    emit(
        'notify',
        next
            ? mentorQuestPublishMessages.publishedToast
            : mentorQuestPublishMessages.unpublishedToast,
    );
};

const onDeleteQuest = async (quest: MentorQuestItem): Promise<void> => {
    if (!window.confirm(mentorQuestAdminMessages.deleteQuestConfirm)) {
        return;
    }

    const success = await removeQuest(quest.id);
    if (!success) {
        emit('notify', mentorQuestAdminMessages.deleteQuestFailed);
        return;
    }

    reloadTeamSections();
    emit('notify', mentorQuestAdminMessages.deleteQuestSuccessToast);
};
</script>

<style scoped>
.mentor-quest-board-links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
}
</style>
