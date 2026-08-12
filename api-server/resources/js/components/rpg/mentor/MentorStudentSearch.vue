<template>
    <form class="mentor-student-search input-group" @submit.prevent="emit('search')">
        <label for="mentor-student-search">{{ label }}</label>
        <div class="mentor-student-search-row">
            <div class="mentor-student-search-field">
                <i :class="buttonIcon" aria-hidden="true"></i>
                <input
                    id="mentor-student-search"
                    :value="modelValue"
                    type="search"
                    :placeholder="placeholder"
                    autocomplete="off"
                    @input="onInput"
                />
            </div>
            <button
                type="submit"
                class="mentor-student-search-btn"
                :disabled="isLoading"
            >
                <i :class="buttonIcon" aria-hidden="true"></i>
                {{ buttonLabel }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { mentorStudentSearchConfig } from '@/constants/mentor/mentor';

withDefaults(
    defineProps<{
        modelValue: string;
        isLoading: boolean;
        label?: string;
        placeholder?: string;
        buttonLabel?: string;
        buttonIcon?: string;
    }>(),
    {
        label: mentorStudentSearchConfig.label,
        placeholder: mentorStudentSearchConfig.placeholder,
        buttonLabel: mentorStudentSearchConfig.buttonLabel,
        buttonIcon: mentorStudentSearchConfig.buttonIcon,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [];
}>();

const onInput = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>
