<template>
    <p v-if="quests.length === 0" class="quest-unit-modal-empty">
        {{ questUnitConfig.emptyQuests }}
    </p>
    <div v-else class="quest-unit-modal-list">
        <QuestUnitModalQuestCard
            v-for="quest in quests"
            :key="quest.id"
            :quest="quest"
        />
    </div>
</template>

<script setup lang="ts">
import type { QuestItem } from '@/types/quest';
import { questUnitConfig } from '@/constants/quests';
import QuestUnitModalQuestCard from '@/components/rpg/QuestUnitModalQuestCard.vue';

defineProps<{
    quests: QuestItem[];
}>();
</script>
