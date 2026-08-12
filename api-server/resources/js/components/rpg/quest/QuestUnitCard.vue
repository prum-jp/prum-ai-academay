<template>
    <article
        class="quest-item quest-unit-item"
        :class="{ 'is-completed': unit.progressStatus === 'completed' }"
    >
        <button type="button" class="quest-body" @click="$emit('open')">
            <div class="quest-title-row">
                <h4 class="quest-title">{{ unit.title }}</h4>
                <UnitProgressStatusBadge :status="unit.progressStatus" size="sm" />
            </div>
        </button>
    </article>
</template>

<script setup lang="ts">
import type { QuestUnitItem } from '@/types/quest/quest';
import UnitProgressStatusBadge from '@/components/rpg/quest-sheet/UnitProgressStatusBadge.vue';

defineProps<{
    unit: QuestUnitItem;
}>();

defineEmits<{
    open: [];
}>();
</script>
