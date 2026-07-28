<template>
    <p v-if="quests.length === 0" class="quest-unit-modal-empty">
        {{ questUnitConfig.emptyQuests }}
    </p>
    <div v-else class="quest-unit-modal-cards">
        <QuestUnitModalQuestCard
            v-for="quest in quests"
            :key="quest.id"
            :quest="quest"
            :disabled="disabled"
            @toggle="$emit('toggle', quest)"
        />
    </div>
</template>

<script setup lang="ts">
import type { QuestItem } from '@/types/quest';
import { questUnitConfig } from '@/constants/quests';
import QuestUnitModalQuestCard from '@/components/rpg/QuestUnitModalQuestCard.vue';

defineProps<{
    quests: QuestItem[];
    disabled?: boolean;
}>();

defineEmits<{
    toggle: [quest: QuestItem];
}>();
</script>
