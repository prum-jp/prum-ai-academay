<template>
    <div v-if="meta && meta.lastPage > 1" class="quest-pagination">
        <button
            type="button"
            class="quest-page-btn"
            :disabled="meta.currentPage <= 1 || disabled"
            @click="$emit('page-change', meta.currentPage - 1)"
        >
            ‹
        </button>
        <span class="quest-page-label">{{ meta.currentPage }} / {{ meta.lastPage }}</span>
        <button
            type="button"
            class="quest-page-btn"
            :disabled="meta.currentPage >= meta.lastPage || disabled"
            @click="$emit('page-change', meta.currentPage + 1)"
        >
            ›
        </button>
    </div>
</template>

<script setup lang="ts">
export interface PaginationMeta {
    currentPage: number;
    lastPage: number;
}

defineProps<{
    meta: PaginationMeta | null;
    disabled?: boolean;
}>();

defineEmits<{
    'page-change': [page: number];
}>();
</script>
