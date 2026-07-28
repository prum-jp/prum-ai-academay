<template>
    <RpgCard
        :title="mentorQuestBoardCardConfig.title"
        :icon="mentorQuestBoardCardConfig.icon"
    >
        <template #title-extra>
            <button
                type="button"
                class="mentor-register-link"
                @click="isCreateModalOpen = true"
            >
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                {{ mentorQuestBoardCardConfig.createButtonLabel }}
            </button>
        </template>

        <p class="mentor-message">{{ mentorQuestBoardCardConfig.description }}</p>

        <div class="quest-notebook mentor-quest-admin-board">
            <MentorQuestAdminSection
                v-if="personalDefinition"
                :title="personalDefinition.title"
                :icon="personalDefinition.icon"
                :meta="personalUnits.meta"
                :is-empty="personalUnits.units.length === 0"
                :empty-message="mentorQuestAdminMessages.emptyUnits"
                :is-loading="personalUnits.isLoading"
                :error="personalUnits.error"
                @page-change="loadPersonalUnits"
            >
                <MentorQuestAdminUnitCard
                    v-for="unit in personalUnits.units"
                    :key="unit.id"
                    :unit="unit"
                    :disabled="isDeleting"
                    @edit="openUnitEdit(unit)"
                    @delete="onDeleteUnit(unit)"
                    @toggle-publish="onToggleUnitPublish(unit)"
                />
            </MentorQuestAdminSection>

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
                    @edit="openQuestEdit(quest)"
                    @delete="onDeleteQuest(quest)"
                    @toggle-publish="onToggleQuestPublish(definition.type, quest)"
                />
            </MentorQuestAdminSection>
        </div>

        <MentorQuestCreateModal
            :open="isCreateModalOpen"
            @close="closeCreateModal"
            @created="handleCreated"
            @imported="handleImported"
        />

        <MentorQuestEditModal
            :open="isEditModalOpen"
            :kind="editKind"
            :unit="editUnit"
            :quest="editQuest"
            @close="closeEditModal"
            @updated="handleUpdated"
        />
    </RpgCard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import {
    mentorQuestAdminMessages,
    mentorQuestAdminSectionDefinitions,
    mentorQuestBoardCardConfig,
    mentorQuestPublishMessages,
    type NonPersonalQuestType,
} from '@/constants/questAdmin';
import { questImportMessages } from '@/constants/questImport';
import type { MentorQuestItem, MentorQuestUnitItem } from '@/types/questAdmin';
import { useMentorQuestCatalog } from '@/composables/useMentorQuestCatalog';
import { useMentorQuestDelete } from '@/composables/useMentorQuestEdit';
import MentorQuestAdminItemCard from '@/components/rpg/MentorQuestAdminItemCard.vue';
import MentorQuestAdminSection from '@/components/rpg/MentorQuestAdminSection.vue';
import MentorQuestAdminUnitCard from '@/components/rpg/MentorQuestAdminUnitCard.vue';
import MentorQuestCreateModal from '@/components/rpg/MentorQuestCreateModal.vue';
import MentorQuestEditModal from '@/components/rpg/MentorQuestEditModal.vue';
import RpgCard from '@/components/rpg/RpgCard.vue';

const emit = defineEmits<{
    notify: [message: string];
}>();

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editKind = ref<'unit' | 'quest'>('unit');
const editUnit = ref<MentorQuestUnitItem | null>(null);
const editQuest = ref<MentorQuestItem | null>(null);

const boardSectionDefinitions = mentorQuestAdminSectionDefinitions.filter(
    (definition): definition is typeof definition & { type: NonPersonalQuestType } =>
        definition.type !== 'personal',
);

const personalDefinition = mentorQuestAdminSectionDefinitions.find(
    (definition) => definition.type === 'personal',
);

const {
    personalUnits,
    sections,
    loadPersonalUnits,
    loadSection,
    setUnitPublished,
    setQuestPublished,
    reloadAll,
} = useMentorQuestCatalog();
const { isDeleting, removeUnit, removeQuest } = useMentorQuestDelete();

const closeCreateModal = (): void => {
    isCreateModalOpen.value = false;
};

const handleCreated = (kind: 'unit' | 'quest'): void => {
    closeCreateModal();
    reloadAll();
    emit(
        'notify',
        kind === 'unit'
            ? mentorQuestAdminMessages.createUnitSuccessToast
            : mentorQuestAdminMessages.createQuestSuccessToast,
    );
};

const handleImported = (): void => {
    closeCreateModal();
    reloadAll();
    emit('notify', questImportMessages.applySuccess);
};

const openUnitEdit = (unit: MentorQuestUnitItem): void => {
    editKind.value = 'unit';
    editUnit.value = unit;
    editQuest.value = null;
    isEditModalOpen.value = true;
};

const openQuestEdit = (quest: MentorQuestItem): void => {
    editKind.value = 'quest';
    editQuest.value = quest;
    editUnit.value = null;
    isEditModalOpen.value = true;
};

const closeEditModal = (): void => {
    isEditModalOpen.value = false;
};

const handleUpdated = (kind: 'unit' | 'quest'): void => {
    closeEditModal();
    reloadAll();
    emit(
        'notify',
        kind === 'unit'
            ? mentorQuestAdminMessages.updateUnitSuccessToast
            : mentorQuestAdminMessages.updateQuestSuccessToast,
    );
};

const onToggleUnitPublish = async (unit: MentorQuestUnitItem): Promise<void> => {
    const next = !unit.isPublished;
    const success = await setUnitPublished(unit, next);
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

const onDeleteUnit = async (unit: MentorQuestUnitItem): Promise<void> => {
    if (!window.confirm(mentorQuestAdminMessages.deleteUnitConfirm)) {
        return;
    }

    const success = await removeUnit(unit.id);
    if (!success) {
        emit('notify', mentorQuestAdminMessages.deleteUnitFailed);
        return;
    }

    reloadAll();
    emit('notify', mentorQuestAdminMessages.deleteUnitSuccessToast);
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

    reloadAll();
    emit('notify', mentorQuestAdminMessages.deleteQuestSuccessToast);
};
</script>
