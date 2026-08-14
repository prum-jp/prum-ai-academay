<template>
    <RpgCard
        :title="mentorToolBoardCardConfig.title"
        :icon="mentorToolBoardCardConfig.icon"
    >
        <template #title-extra>
            <button
                type="button"
                class="mentor-register-link"
                @click="openCreateModal"
            >
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                {{ mentorToolBoardCardConfig.createButtonLabel }}
            </button>
        </template>

        <p class="mentor-message">{{ mentorToolBoardCardConfig.description }}</p>

        <div class="mentor-tool-list">
            <AsyncState
                :is-loading="isLoading"
                :error="error"
                :is-empty="tools.length === 0"
                :loading-message="mentorToolMessages.loading"
                :empty-message="mentorToolMessages.emptyList"
            >
                <MentorToolCard
                    v-for="tool in tools"
                    :key="tool.id"
                    :tool="tool"
                    @edit="openEditModal"
                />
            </AsyncState>
        </div>

        <MentorToolFormModal
            :open="isFormModalOpen"
            :tool="editingTool"
            @close="closeFormModal"
            @saved="handleSaved"
        />
    </RpgCard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { MentorTool } from '@/types/mentor-quest/questAdmin';
import { mentorToolBoardCardConfig, mentorToolMessages } from '@/constants/mentor-tools/toolAdmin';
import { useMentorToolCatalog } from '@/composables/mentor-tools/useMentorToolCatalog';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import MentorToolCard from '@/components/rpg/mentor-tools/MentorToolCard.vue';
import MentorToolFormModal from '@/components/rpg/mentor-tools/MentorToolFormModal.vue';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';

const emit = defineEmits<{
    notify: [message: string];
}>();

const { tools, isLoading, error, loadTools } = useMentorToolCatalog();
const isFormModalOpen = ref(false);
const editingTool = ref<MentorTool | null>(null);

const openCreateModal = (): void => {
    editingTool.value = null;
    isFormModalOpen.value = true;
};

const openEditModal = (tool: MentorTool): void => {
    editingTool.value = tool;
    isFormModalOpen.value = true;
};

const closeFormModal = (): void => {
    isFormModalOpen.value = false;
    editingTool.value = null;
};

const handleSaved = async (tool: MentorTool): Promise<void> => {
    const wasEdit = editingTool.value != null;
    closeFormModal();
    await loadTools();
    emit(
        'notify',
        wasEdit
            ? `${tool.name} を更新しました！`
            : `${tool.name} を追加しました！`,
    );
};
</script>
