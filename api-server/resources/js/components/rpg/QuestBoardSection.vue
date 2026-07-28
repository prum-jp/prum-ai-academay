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
            :loading-message="questMessages.loading"
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
import type { QuestListMeta } from '@/types/quest';
import { questMessages } from '@/constants/quests';
import AsyncState from '@/components/rpg/AsyncState.vue';
import QuestPagination from '@/components/rpg/QuestPagination.vue';

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
