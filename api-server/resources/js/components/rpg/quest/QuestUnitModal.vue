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

            <QuestUnitQuestList :quests="unit.quests" />
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type { QuestUnitItem } from '@/types/quest/quest';
import { useQuestUnitDetail } from '@/composables/quest/useQuestUnitDetail';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';
import QuestUnitModalHeader from '@/components/rpg/quest/QuestUnitModalHeader.vue';
import QuestUnitQuestList from '@/components/rpg/quest/QuestUnitQuestList.vue';

const props = defineProps<{
    unit: QuestUnitItem | null;
}>();

defineEmits<{
    close: [];
}>();

const { unitTitle, growthStatusText } = useQuestUnitDetail(toRef(props, 'unit'));
</script>
