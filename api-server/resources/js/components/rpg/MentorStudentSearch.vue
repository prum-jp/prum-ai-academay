<template>
    <form class="mentor-student-search input-group" @submit.prevent="emit('search')">
        <label for="mentor-student-search">{{ mentorStudentSearchConfig.label }}</label>
        <div class="mentor-student-search-row">
            <div class="mentor-student-search-field">
                <i :class="mentorStudentSearchConfig.buttonIcon" aria-hidden="true"></i>
                <input
                    id="mentor-student-search"
                    :value="modelValue"
                    type="search"
                    :placeholder="mentorStudentSearchConfig.placeholder"
                    autocomplete="off"
                    @input="onInput"
                />
            </div>
            <button
                type="submit"
                class="mentor-student-search-btn"
                :disabled="isLoading"
            >
                <i :class="mentorStudentSearchConfig.buttonIcon" aria-hidden="true"></i>
                {{ mentorStudentSearchConfig.buttonLabel }}
            </button>
        </div>
    </form>
</template>

<script setup lang="ts">
import { mentorStudentSearchConfig } from '@/constants/mentor';

defineProps<{
    modelValue: string;
    isLoading: boolean;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    search: [];
}>();

const onInput = (event: Event): void => {
    const target = event.target as HTMLInputElement;
    emit('update:modelValue', target.value);
};
</script>
