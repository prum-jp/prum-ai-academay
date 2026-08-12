<template>
    <section class="quest-section">
        <header class="quest-section-header">
            <i :class="icon"></i>
            <h3>{{ title }}</h3>
        </header>

        <AsyncState
            :is-loading="isLoading"
            :error="error"
            :is-empty="isEmpty"
            :loading-message="mentorQuestAdminMessages.loading"
            :empty-message="emptyMessage"
        >
            <div class="quest-list">
                <slot />
            </div>
        </AsyncState>

        <QuestPagination
            :meta="meta"
            :disabled="isLoading"
            @page-change="$emit('page-change', $event)"
        />
    </section>
</template>

<script setup lang="ts">
import type { QuestListMeta } from '@/types/quest/quest';
import { mentorQuestAdminMessages } from '@/constants/mentor-quest/questAdmin';
import AsyncState from '@/components/rpg/shared/AsyncState.vue';
import QuestPagination from '@/components/rpg/shared/QuestPagination.vue';

defineProps<{
    title: string;
    icon: string;
    meta: QuestListMeta | null;
    isEmpty: boolean;
    emptyMessage: string;
    isLoading?: boolean;
    error?: string;
}>();

defineEmits<{
    'page-change': [page: number];
}>();
</script>
