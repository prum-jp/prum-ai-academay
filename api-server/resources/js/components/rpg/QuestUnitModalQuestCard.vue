<template>
    <article
        class="quest-unit-modal-card"
        :class="{ 'is-completed': quest.isCompleted }"
    >
        <div class="quest-unit-modal-card-top">
            <QuestCheckbox
                :checked="quest.isCompleted"
                :disabled="quest.isLocked || disabled"
                @toggle="$emit('toggle')"
            />

            <div class="quest-unit-modal-card-badges">
                <span v-if="quest.tool" class="quest-unit-tool-badge">
                    <i :class="toolIcon" aria-hidden="true"></i>
                    {{ quest.tool.name }}
                </span>
            </div>
        </div>

        <h3 class="quest-unit-modal-card-title">{{ quest.title }}</h3>

        <p v-if="quest.description" class="quest-unit-modal-card-description">
            {{ quest.description }}
        </p>

        <section class="quest-unit-clear-box">
            <header class="quest-unit-clear-box-header">
                <span class="quest-unit-clear-icon" aria-hidden="true">
                    <i class="fa-solid fa-check"></i>
                </span>
                <i class="fa-solid fa-trophy quest-unit-clear-trophy" aria-hidden="true"></i>
                <h4>{{ questUnitConfig.clearConditionTitle }}</h4>
            </header>
            <p class="quest-unit-clear-box-body">
                {{ clearConditionText }}
            </p>
        </section>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { QuestItem } from '@/types/quest';
import { questUnitConfig } from '@/constants/quests';
import QuestCheckbox from '@/components/rpg/QuestCheckbox.vue';

const props = defineProps<{
    quest: QuestItem;
    disabled?: boolean;
}>();

defineEmits<{
    toggle: [];
}>();

const toolIcon = computed(
    () => props.quest.tool?.icon ?? questUnitConfig.defaultToolIcon,
);

const clearConditionText = computed((): string => {
    if (props.quest.clearCondition.trim() !== '') {
        return props.quest.clearCondition;
    }

    return questUnitConfig.emptyClearCondition;
});
</script>
