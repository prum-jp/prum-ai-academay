<template>
    <div class="input-group mentor-quest-create-type-field">
        <label :for="id">{{ mentorQuestCreatePageConfig.typeLabel }}</label>
        <select
            :id="id"
            class="quest-sheet-create-meta-input mentor-quest-create-type-select"
            :value="modelValue"
            :disabled="disabled"
            @change="onChange"
        >
            <option
                v-for="option in mentorQuestCreateTypeOptions"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
    </div>
</template>

<script setup lang="ts">
import {
    mentorQuestCreatePageConfig,
    mentorQuestCreateTypeOptions,
} from '@/constants/mentor-quest/questAdmin';
import type { MentorQuestCreateType } from '@/types/mentor-quest/questAdmin';

withDefaults(
    defineProps<{
        modelValue: MentorQuestCreateType;
        disabled?: boolean;
        id?: string;
    }>(),
    {
        disabled: false,
        id: 'quest-create-type',
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: MentorQuestCreateType];
}>();

const onChange = (event: Event): void => {
    emit('update:modelValue', (event.target as HTMLSelectElement).value as MentorQuestCreateType);
};
</script>
