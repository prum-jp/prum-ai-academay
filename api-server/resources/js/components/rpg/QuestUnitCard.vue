<template>
    <article
        class="quest-item quest-unit-item"
        :class="{ 'is-completed': unit.isCompleted }"
    >
        <QuestCheckbox
            :checked="unit.isCompleted"
            :indeterminate="isIndeterminate"
            :disabled="disabled"
            @toggle="$emit('toggle')"
        />

        <button type="button" class="quest-body" @click="$emit('open')">
            <div class="quest-title-row">
                <h4 class="quest-title">{{ unit.title }}</h4>
                <span class="quest-badge is-default">
                    {{ questUnitConfig.progressLabel(unit.completedCount, unit.totalCount) }}
                </span>
            </div>

            <p v-if="unit.rewardText" class="quest-reward">
                <i class="fa-solid fa-star"></i>
                {{ unit.rewardText }}
            </p>
        </button>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { QuestUnitItem } from '@/types/quest';
import { questUnitConfig } from '@/constants/quests';
import { isUnitIndeterminate } from '@/utils/questUnitProgress';
import QuestCheckbox from '@/components/rpg/QuestCheckbox.vue';

const props = defineProps<{
    unit: QuestUnitItem;
    disabled?: boolean;
}>();

defineEmits<{
    open: [];
    toggle: [];
}>();

const isIndeterminate = computed(() => isUnitIndeterminate(props.unit));
</script>
