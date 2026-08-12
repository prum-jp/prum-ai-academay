<template>
    <RpgModal
        :open="Boolean(unit)"
        :title="unitTitle"
        wide
        @close="$emit('close')"
    >
        <template v-if="unit">
            <QuestUnitQuestList :quests="unit.quests" />
        </template>
    </RpgModal>
</template>

<script setup lang="ts">
import { toRef } from 'vue';
import type { QuestUnitItem } from '@/types/quest/quest';
import { useQuestUnitDetail } from '@/composables/quest/useQuestUnitDetail';
import RpgModal from '@/components/rpg/shared/RpgModal.vue';
import QuestUnitQuestList from '@/components/rpg/quest/QuestUnitQuestList.vue';

const props = defineProps<{
    unit: QuestUnitItem | null;
}>();

defineEmits<{
    close: [];
}>();

const { unitTitle } = useQuestUnitDetail(toRef(props, 'unit'));
</script>
