import type { QuestSheetMetaRow } from '@/components/rpg/quest-sheet/QuestSheetMetaSidebar.vue';
import type { QuestItem } from '@/types/quest/quest';
import type { SkillKey } from '@/constants/shared/skills';
import type { QuestTier } from '@/constants/quest/questTier';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import {
    experiencePointsFromDifficulty,
    formatExperiencePoints,
    formatQuestDifficultyStars,
} from '@/utils/quest/questDifficulty';
import { formatQuestTierDisplay } from '@/constants/quest/questTier';
import { formatSkillGrants } from '@/utils/quest/skillGrants';

export interface QuestMetaDisplaySource {
    tool?: { name?: string | null } | null;
    tools?: Array<{ name?: string | null }>;
    difficulty: number | null;
    experiencePoints?: number;
    questTier?: QuestTier;
    unlockLevel?: number | null;
    skillGrants?: SkillKey[];
    badgeLabel?: string | null;
}

export const resolveQuestExperiencePoints = (source: QuestMetaDisplaySource): number => {
    const stored = source.experiencePoints ?? 0;
    if (stored > 0) {
        return stored;
    }

    return experiencePointsFromDifficulty(source.difficulty);
};

export const formatQuestDifficulty = (source: QuestMetaDisplaySource): string =>
    formatQuestDifficultyStars(source.difficulty);

export const formatQuestLevelRange = (source: QuestMetaDisplaySource): string =>
    formatQuestTierDisplay(source.questTier, source.unlockLevel ?? null);

export const formatQuestSkillLabel = (source: QuestMetaDisplaySource): string => {
    if (source.badgeLabel?.trim()) {
        return source.badgeLabel.trim();
    }

    return formatSkillGrants(source.skillGrants ?? []);
};

export const formatUnitSkillLabel = (skillGrants: SkillKey[]): string =>
    formatSkillGrants(skillGrants);

export const collectQuestToolNames = (source: QuestMetaDisplaySource): string[] => {
    const toolNames = (source.tools ?? [])
        .map((tool) => tool.name?.trim())
        .filter((name): name is string => Boolean(name));

    if (toolNames.length > 0) {
        return toolNames;
    }

    const single = source.tool?.name?.trim();
    return single ? [single] : [];
};

export const formatQuestToolLabel = (source: QuestMetaDisplaySource): string => {
    const toolNames = collectQuestToolNames(source);

    if (toolNames.length === 0) {
        return '—';
    }

    return toolNames.join('\n');
};

export const buildQuestMetaRows = (quest: QuestItem | QuestMetaDisplaySource): QuestSheetMetaRow[] => {
    const { metaLabels } = questSheetConfig;
    const toolNames = collectQuestToolNames(quest);

    return [
        {
            label: metaLabels.recommendedTool,
            value: toolNames.length > 0 ? toolNames.join('\n') : '—',
            lines: toolNames.length > 0 ? toolNames : undefined,
        },
        {
            label: metaLabels.difficulty,
            value: formatQuestDifficulty(quest),
        },
        {
            label: metaLabels.questTier,
            value: formatQuestLevelRange(quest),
        },
        {
            label: metaLabels.experiencePoints,
            value: formatExperiencePoints(resolveQuestExperiencePoints(quest)),
        },
        {
            label: metaLabels.acquiredSkill,
            value: formatQuestSkillLabel(quest),
        },
    ];
};
