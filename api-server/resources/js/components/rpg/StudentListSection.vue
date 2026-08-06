<template>
    <div class="mentor-student-list">
        <MentorStudentSearch
            :model-value="searchQuery"
            :is-loading="isLoading"
            :label="searchLabel"
            :placeholder="searchPlaceholder"
            @update:model-value="$emit('update:searchQuery', $event)"
            @search="$emit('search')"
        />

        <AsyncState
            :is-loading="isLoading"
            :error="error"
            :is-empty="items.length === 0"
            :loading-message="loadingMessage"
            :empty-message="emptyMessage"
        >
            <template v-for="item in items" :key="resolveKey(item)">
                <slot name="item" :item="item" />
            </template>
        </AsyncState>

        <QuestPagination
            :meta="meta"
            :disabled="paginationDisabled"
            @page-change="$emit('page-change', $event)"
        />

        <slot name="footer" />
    </div>
</template>

<script setup lang="ts">
import type { PaginationMeta } from '@/types/pagination';
import AsyncState from '@/components/rpg/AsyncState.vue';
import MentorStudentSearch from '@/components/rpg/MentorStudentSearch.vue';
import QuestPagination from '@/components/rpg/QuestPagination.vue';

const props = defineProps<{
    items: unknown[];
    meta: PaginationMeta | null;
    searchQuery: string;
    isLoading: boolean;
    error: string;
    emptyMessage: string;
    loadingMessage: string;
    searchLabel: string;
    searchPlaceholder: string;
    paginationDisabled?: boolean;
    itemKey?: (item: unknown) => string | number;
}>();

defineEmits<{
    'update:searchQuery': [value: string];
    search: [];
    'page-change': [page: number];
}>();

const resolveKey = (item: unknown): string | number => {
    if (props.itemKey) {
        return props.itemKey(item);
    }

    if (typeof item === 'object' && item !== null && 'id' in item) {
        return (item as { id: string | number }).id;
    }

    return String(item);
};
</script>
