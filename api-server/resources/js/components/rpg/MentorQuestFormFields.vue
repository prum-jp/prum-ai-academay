<template>
    <div class="input-group">
        <label :for="`${idPrefix}-title`">{{ mentorQuestFormLabels.title }}</label>
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
        <label :for="`${idPrefix}-description`">{{ mentorQuestFormLabels.description }}</label>
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
        <label :for="`${idPrefix}-clear-condition`">{{ mentorQuestFormLabels.clearCondition }}</label>
        <textarea
            :id="`${idPrefix}-clear-condition`"
            v-model="form.clearCondition"
            rows="3"
            maxlength="2000"
            :placeholder="placeholders?.clearCondition"
            :disabled="disabled"
        />
    </div>

    <div class="input-group">
        <label :for="`${idPrefix}-reward-text`">{{ mentorQuestFormLabels.rewardText }}</label>
        <input
            :id="`${idPrefix}-reward-text`"
            v-model="form.rewardText"
            type="text"
            maxlength="500"
            :placeholder="placeholders?.rewardText"
            :disabled="disabled"
        />
    </div>

    <div class="input-group">
        <label :for="`${idPrefix}-badge-label`">{{ mentorQuestFormLabels.badgeLabel }}</label>
        <input
            :id="`${idPrefix}-badge-label`"
            v-model="form.badgeLabel"
            type="text"
            maxlength="255"
            :placeholder="placeholders?.badgeLabel"
            :disabled="disabled"
        />
    </div>

    <div class="input-group">
        <label :for="`${idPrefix}-unlock-level`">{{ mentorQuestFormLabels.unlockLevel }}</label>
        <input
            :id="`${idPrefix}-unlock-level`"
            v-model.number="form.unlockLevel"
            type="number"
            min="1"
            max="99"
            :placeholder="placeholders?.unlockLevel"
            :disabled="disabled"
        />
    </div>

    <label class="mentor-checkbox-row">
        <input
            v-model="form.isRequired"
            type="checkbox"
            :disabled="disabled"
        />
        {{ mentorQuestFormLabels.isRequired }}
    </label>

    <section class="mentor-reward-section">
        <h4>{{ mentorQuestFormLabels.rewardsTitle }}</h4>
        <MentorQuestRewardFields
            :rewards="form.rewards"
            :labels="mentorQuestFormLabels"
            :field-prefix="`${idPrefix}-reward`"
            :disabled="disabled"
            @add="addReward"
            @remove="removeReward"
        />
    </section>
</template>

<script setup lang="ts">
import type { MentorQuestRewardInput } from '@/types/questAdmin';
import { createEmptyReward, mentorQuestFormLabels } from '@/constants/questAdmin';
import MentorQuestRewardFields from '@/components/rpg/MentorQuestRewardFields.vue';

interface QuestFormLike {
    title: string;
    description: string;
    clearCondition: string;
    rewardText: string;
    badgeLabel: string;
    unlockLevel: number | null;
    isRequired: boolean;
    rewards: MentorQuestRewardInput[];
}

const props = withDefaults(
    defineProps<{
        form: QuestFormLike;
        fieldErrors: Record<string, string>;
        idPrefix: string;
        disabled?: boolean;
        placeholders?: {
            title?: string;
            description?: string;
            clearCondition?: string;
            rewardText?: string;
            badgeLabel?: string;
            unlockLevel?: string;
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
