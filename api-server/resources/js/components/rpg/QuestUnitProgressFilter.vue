<template>
    <div class="quest-unit-progress-filter" role="group" :aria-label="questMessages.unitProgressFilterLabel">
        <button
            v-for="option in unitProgressFilterOptions"
            :key="option.value"
            type="button"
            class="quest-unit-progress-filter-btn"
            :class="{ 'is-active': modelValue === option.value }"
            @click="selectFilter(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>

<script setup lang="ts">
import { questMessages } from '@/constants/quests';
import {
    type UnitProgressFilter,
    unitProgressFilterOptions,
} from '@/constants/unitProgress';

const modelValue = defineModel<UnitProgressFilter>({ required: true });

const selectFilter = (value: UnitProgressFilter): void => {
    if (modelValue.value === value) {
        return;
    }

    modelValue.value = value;
};
</script>
