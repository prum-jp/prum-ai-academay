<template>
    <div class="badge-summary-wrap">
        <div class="badge-summary">
            <i class="fa-solid fa-trophy"></i>
            <span>獲得バッジ数: {{ earnedCount }} / {{ totalCount }}</span>
        </div>
    </div>
</template>

<script setup lang="ts">
defineProps<{
    earnedCount: number;
    totalCount: number;
}>();
</script>
