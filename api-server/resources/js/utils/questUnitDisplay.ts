import type { QuestReward } from '@/types/quest';
import { statDefinitions } from '@/constants/stats';
import { growthStatLabels } from '@/constants/quests';

const formatUnitCode = (sortOrder: number): string =>
    `UNIT ${String(sortOrder).padStart(2, '0')}`;

export const formatUnitDisplayTitle = (sortOrder: number, title: string): string =>
    `${formatUnitCode(sortOrder)}：${title}`;

export const formatGrowthStatLabel = (stat: string): string =>
    growthStatLabels[stat] ?? statDefinitions.find((item) => item.key === stat)?.label ?? stat;

export const formatGrowthStatusText = (rewards: QuestReward[]): string => {
    if (rewards.length === 0) {
        return '';
    }

    const labels = rewards.map((reward) => `【${formatGrowthStatLabel(reward.stat)}】`);
    return `成長ステータス：${labels.join(' ')}`;
};
