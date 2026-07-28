<template>
    <RpgCard
        :title="mentorToolBoardCardConfig.title"
        :icon="mentorToolBoardCardConfig.icon"
    >
        <template #title-extra>
            <button
                type="button"
                class="mentor-register-link"
                @click="isCreateModalOpen = true"
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
                <MentorToolCard v-for="tool in tools" :key="tool.id" :tool="tool" />
            </AsyncState>
        </div>

        <MentorToolCreateModal
            :open="isCreateModalOpen"
            @close="closeCreateModal"
            @created="handleCreated"
        />
    </RpgCard>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import type { MentorTool } from '@/types/questAdmin';
import { mentorToolBoardCardConfig, mentorToolMessages } from '@/constants/toolAdmin';
import { useMentorToolCatalog } from '@/composables/useMentorToolCatalog';
import AsyncState from '@/components/rpg/AsyncState.vue';
import MentorToolCard from '@/components/rpg/MentorToolCard.vue';
import MentorToolCreateModal from '@/components/rpg/MentorToolCreateModal.vue';
import RpgCard from '@/components/rpg/RpgCard.vue';

const emit = defineEmits<{
    notify: [message: string];
}>();

const { tools, isLoading, error, loadTools } = useMentorToolCatalog();
const isCreateModalOpen = ref(false);

const closeCreateModal = (): void => {
    isCreateModalOpen.value = false;
};

const handleCreated = async (tool: MentorTool): Promise<void> => {
    closeCreateModal();
    await loadTools();
    emit('notify', `${tool.name} を追加しました！`);
};
</script>
