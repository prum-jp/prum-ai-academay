import { computed, type Ref } from 'vue';
import type { QuestUnitItem } from '@/types/quest/quest';
import { questUnitConfig } from '@/constants/quest/quests';
import { formatUnitDisplayTitle, formatUnitSkillGrantsText } from '@/utils/quest/questUnitDisplay';

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

        return formatUnitSkillGrantsText(unit.value.quests);
    });

    return {
        unitTitle,
        growthStatusText,
    };
}
