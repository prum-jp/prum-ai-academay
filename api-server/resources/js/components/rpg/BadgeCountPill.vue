<template>
    <div class="badge-count-pill" title="獲得バッジ数">
        <i class="fa-solid fa-medal"></i>
        <span>{{ earnedCount }} / {{ totalCount }}</span>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        earnedCount?: number;
        totalCount?: number;
    }>(),
    {
        earnedCount: 0,
        totalCount: 0,
    },
);
</script>
