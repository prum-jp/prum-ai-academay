<template>
    <RpgStatusCard
        v-if="isLoading"
        :title="loadingTitle"
        icon="fa-solid fa-spinner"
        :message="loadingMessage"
    />

    <RpgStatusCard
        v-else-if="loadError"
        title="取得失敗"
        icon="fa-solid fa-triangle-exclamation"
        variant="error"
        :message="loadError"
        show-retry
        @retry="$emit('retry')"
    />

    <slot v-else />
</template>

<script setup lang="ts">
import RpgStatusCard from '@/components/rpg/RpgStatusCard.vue';

withDefaults(
    defineProps<{
        isLoading: boolean;
        loadError?: string;
        loadingTitle?: string;
        loadingMessage: string;
    }>(),
    {
        loadError: '',
        loadingTitle: '読み込み中',
    },
);

defineEmits<{
    retry: [];
}>();
</script>
