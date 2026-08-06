import { ref } from 'vue';
import { patchQuestProgress, type QuestProgressRole } from '@/api/questProgress';
import type { QuestProgressStatus } from '@/constants/questProgress';
import type { QuestItem } from '@/types/quest';

export function useQuestProgress() {
    const isUpdating = ref(false);

    const canUpdateQuest = (quest: QuestItem): boolean => !quest.isLocked && !isUpdating.value;

    const updateQuestStatus = async (
        quest: QuestItem,
        status: QuestProgressStatus,
        role: QuestProgressRole = 'student',
    ): Promise<QuestItem | null> => {
        if (role === 'student' && !canUpdateQuest(quest)) {
            return null;
        }

        if (isUpdating.value) {
            return null;
        }

        isUpdating.value = true;

        try {
            return await patchQuestProgress(quest.id, status, role);
        } catch {
            return null;
        } finally {
            isUpdating.value = false;
        }
    };

    const updateQuestsStatus = async (
        quests: QuestItem[],
        status: QuestProgressStatus,
    ): Promise<QuestItem[]> => {
        if (isUpdating.value || quests.length === 0) {
            return [];
        }

        isUpdating.value = true;
        const updatedQuests: QuestItem[] = [];

        try {
            for (const quest of quests) {
                if (quest.isLocked) {
                    continue;
                }

                const updated = await patchQuestProgress(quest.id, status, 'student');
                updatedQuests.push(updated);
            }

            return updatedQuests;
        } catch {
            return updatedQuests;
        } finally {
            isUpdating.value = false;
        }
    };

    return {
        isUpdating,
        updateQuestStatus,
        updateQuestsStatus,
    };
}
