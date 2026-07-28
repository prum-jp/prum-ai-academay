<template>
    <div class="input-group">
        <label :for="`${idPrefix}-title`">{{ mentorQuestUnitFormLabels.title }}</label>
        <input
            :id="`${idPrefix}-title`"
            v-model="form.title"
            type="text"
            required
            maxlength="255"
            :placeholder="placeholders?.title"
            :disabled="disabled"
        />
        <p v-if="fieldErrors.title" class="login-error">{{ fieldErrors.title }}</p>
    </div>

    <div class="input-group">
        <label :for="`${idPrefix}-description`">{{ mentorQuestUnitFormLabels.description }}</label>
        <textarea
            :id="`${idPrefix}-description`"
            v-model="form.description"
            rows="3"
            maxlength="2000"
            :placeholder="placeholders?.description"
            :disabled="disabled"
        />
    </div>

    <div class="input-group">
        <label :for="`${idPrefix}-reward-text`">{{ mentorQuestUnitFormLabels.rewardText }}</label>
        <input
            :id="`${idPrefix}-reward-text`"
            v-model="form.rewardText"
            type="text"
            maxlength="500"
            :placeholder="placeholders?.rewardText"
            :disabled="disabled"
        />
    </div>

    <section class="mentor-reward-section">
        <h4>{{ mentorQuestUnitFormLabels.rewardsTitle }}</h4>
        <MentorQuestRewardFields
            :rewards="form.rewards"
            :labels="mentorQuestUnitFormLabels"
            :field-prefix="`${idPrefix}-reward`"
            :disabled="disabled"
            @add="addReward"
            @remove="removeReward"
        />
    </section>
</template>

<script setup lang="ts">
import type { MentorQuestRewardInput } from '@/types/questAdmin';
import { createEmptyReward, mentorQuestUnitFormLabels } from '@/constants/questAdmin';
import MentorQuestRewardFields from '@/components/rpg/MentorQuestRewardFields.vue';

interface UnitFormLike {
    title: string;
    description: string;
    rewardText: string;
    rewards: MentorQuestRewardInput[];
}

const props = withDefaults(
    defineProps<{
        form: UnitFormLike;
        fieldErrors: Record<string, string>;
        idPrefix: string;
        disabled?: boolean;
        placeholders?: {
            title?: string;
            description?: string;
            rewardText?: string;
        };
    }>(),
    {
        disabled: false,
        placeholders: undefined,
    },
);

const addReward = (): void => {
    props.form.rewards.push(createEmptyReward());
};

const removeReward = (index: number): void => {
    props.form.rewards.splice(index, 1);
};
</script>
