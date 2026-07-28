import { ref } from 'vue';
import { toggleQuestProgress } from '@/api/quests';
import type { QuestItem } from '@/types/quest';
import { useGameAudio } from '@/composables/useGameAudio';

export function useQuestToggle() {
    const isUpdating = ref(false);
    const { playSound } = useGameAudio();

    const canToggleQuest = (quest: QuestItem): boolean => !quest.isLocked && !isUpdating.value;

    const toggleQuest = async (quest: QuestItem): Promise<QuestItem | null> => {
        if (!canToggleQuest(quest)) {
            playSound('down');
            return null;
        }

        isUpdating.value = true;

        try {
            const updated = await toggleQuestProgress(quest.id);
            playSound(updated.isCompleted ? 'click' : 'down');
            return updated;
        } catch {
            playSound('down');
            return null;
        } finally {
            isUpdating.value = false;
        }
    };

    const toggleQuests = async (quests: QuestItem[]): Promise<QuestItem[]> => {
        if (isUpdating.value || quests.length === 0) {
            return [];
        }

        isUpdating.value = true;
        const updatedQuests: QuestItem[] = [];

        try {
            for (const quest of quests) {
                const updated = await toggleQuestProgress(quest.id);
                updatedQuests.push(updated);
            }

            const targetCompleted = updatedQuests.at(-1)?.isCompleted ?? false;
            playSound(targetCompleted ? 'click' : 'down');
            return updatedQuests;
        } catch {
            playSound('down');
            return updatedQuests;
        } finally {
            isUpdating.value = false;
        }
    };

    return {
        isUpdating,
        toggleQuest,
        toggleQuests,
        playSound,
    };
}
