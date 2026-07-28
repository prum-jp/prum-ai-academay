import { computed, type Ref } from 'vue';
import type { QuestUnitItem } from '@/types/quest';
import { questUnitConfig } from '@/constants/quests';
import { formatGrowthStatusText, formatUnitDisplayTitle } from '@/utils/questUnitDisplay';

export function useQuestUnitDetail(unit: Ref<QuestUnitItem | null>) {
    const unitTitle = computed((): string => {
        if (!unit.value) {
            return questUnitConfig.fallbackTitle;
        }

        return formatUnitDisplayTitle(unit.value.sortOrder, unit.value.title);
    });

    const growthStatusText = computed((): string => {
        if (!unit.value) {
            return '';
        }

        return formatGrowthStatusText(unit.value.rewards);
    });

    return {
        unitTitle,
        growthStatusText,
    };
}
