<template>
    <RpgModal
        :open="Boolean(unit)"
        headerless
        wide
        @close="$emit('close')"
    >
        <template v-if="unit">
            <QuestUnitModalHeader
                :unit-title="unitTitle"
                :growth-status-text="growthStatusText"
            />

            <QuestUnitQuestList
                :quests="unit.quests"
                :disabled="isUpdating"
                @toggle="$emit('toggle', $event)"
            />
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type { QuestItem, QuestUnitItem } from '@/types/quest';
import { useQuestUnitDetail } from '@/composables/useQuestUnitDetail';
import RpgModal from '@/components/rpg/RpgModal.vue';
import QuestUnitModalHeader from '@/components/rpg/QuestUnitModalHeader.vue';
import QuestUnitQuestList from '@/components/rpg/QuestUnitQuestList.vue';

const props = defineProps<{
    unit: QuestUnitItem | null;
    isUpdating?: boolean;
}>();

defineEmits<{
    close: [];
    toggle: [quest: QuestItem];
}>();

const { unitTitle, growthStatusText } = useQuestUnitDetail(toRef(props, 'unit'));
</script>
