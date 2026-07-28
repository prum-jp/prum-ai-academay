<template>
    <p v-if="isLoading" class="async-state-note">{{ loadingMessage }}</p>
    <p v-else-if="error" class="login-error">{{ error }}</p>
    <p v-else-if="isEmpty" class="async-state-note">{{ emptyMessage }}</p>
    <slot v-else />
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        isLoading?: boolean;
        error?: string;
        isEmpty?: boolean;
        loadingMessage?: string;
        emptyMessage?: string;
    }>(),
    {
        isLoading: false,
        error: '',
        isEmpty: false,
        loadingMessage: '読み込み中...',
        emptyMessage: 'データがありません。',
    },
);
</script>
