<template>
    <div class="mentor-reward-rows">
        <div
            v-for="(reward, index) in rewards"
            :key="index"
            class="mentor-reward-row"
        >
            <div class="input-group mentor-reward-field">
                <label :for="`${fieldPrefix}-stat-${index}`">{{ labels.stat }}</label>
                <select
                    :id="`${fieldPrefix}-stat-${index}`"
                    v-model="reward.stat"
                    :disabled="disabled"
                >
                    <option
                        v-for="option in mentorQuestStatOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="input-group mentor-reward-field mentor-reward-points">
                <label :for="`${fieldPrefix}-points-${index}`">{{ labels.points }}</label>
                <input
                    :id="`${fieldPrefix}-points-${index}`"
                    v-model.number="reward.points"
                    type="number"
                    min="1"
                    max="99"
                    :disabled="disabled"
                />
            </div>

            <button
                type="button"
                class="mentor-reward-remove"
                :disabled="disabled"
                @click="$emit('remove', index)"
            >
                {{ labels.removeReward }}
            </button>
        </div>

        <button
            type="button"
            class="mentor-reward-add"
            :disabled="disabled"
            @click="$emit('add')"
        >
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
            {{ labels.addReward }}
        </button>
    </div>
</template>

<script setup lang="ts">
import type { MentorQuestRewardInput } from '@/types/questAdmin';
import { mentorQuestStatOptions } from '@/constants/questAdmin';

defineProps<{
    rewards: MentorQuestRewardInput[];
    labels: {
        stat: string;
        points: string;
        addReward: string;
        removeReward: string;
    };
    fieldPrefix: string;
    disabled?: boolean;
}>();

defineEmits<{
    add: [];
    remove: [index: number];
}>();
</script>
