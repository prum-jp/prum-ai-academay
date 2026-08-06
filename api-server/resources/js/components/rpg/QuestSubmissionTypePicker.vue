<template>
    <div
        class="quest-submission-type-picker"
        role="radiogroup"
        :aria-label="questSubmissionMessages.typeLabel"
    >
        <button
            v-for="option in questSubmissionTypeOptions"
            :key="option.value"
            type="button"
            role="radio"
            class="quest-submission-type-option"
            :class="{ 'is-active': modelValue === option.value }"
            :aria-checked="modelValue === option.value"
            :disabled="disabled"
            @click="selectType(option.value)"
        >
            <span class="quest-submission-type-option-icon" aria-hidden="true">
                <i :class="option.icon"></i>
            </span>
            <span class="quest-submission-type-option-label">{{ option.label }}</span>
        </button>
    </div>
</template>

<script setup lang="ts">
import {
    questSubmissionMessages,
    questSubmissionTypeOptions,
    type QuestSubmissionType,
} from '@/constants/questSubmission';

const modelValue = defineModel<QuestSubmissionType>({ required: true });

withDefaults(
    defineProps<{
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    },
);

const selectType = (value: QuestSubmissionType): void => {
    if (modelValue.value === value) {
        return;
    }

    modelValue.value = value;
};
</script>
