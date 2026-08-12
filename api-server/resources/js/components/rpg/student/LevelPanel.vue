<template>
    <div class="level-panel">
        <div class="level-badge">{{ levelTitle }}</div>
        <p v-if="xpLabel" class="level-xp-label">{{ xpLabel }}</p>
        <div class="xp-bar-container">
            <div class="xp-bar-fill" :style="{ width: `${progressPercent}%` }"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    levelTitle: string;
    progressPercent: number;
    totalXp?: number;
    xpNextLevelMin?: number | null;
}>();

const xpLabel = computed((): string | null => {
    if (props.totalXp === undefined) {
        return null;
    }

    if (props.xpNextLevelMin === null || props.xpNextLevelMin === undefined) {
        return `${props.totalXp.toLocaleString('ja-JP')} XP（最大レベル）`;
    }

    const remaining = Math.max(0, props.xpNextLevelMin - props.totalXp);

    return `${props.totalXp.toLocaleString('ja-JP')} XP（次のレベルまで ${remaining.toLocaleString('ja-JP')} XP）`;
});
</script>

<style scoped>
.level-xp-label {
    margin: 6px 0 8px;
    font-size: 12px;
    font-weight: 700;
    color: var(--rpg-muted, #6b7280);
}
</style>
