<template>
    <label class="mentor-toggle" :class="{ 'is-on': modelValue }">
        <input
            type="checkbox"
            class="mentor-toggle-input"
            :checked="modelValue"
            :disabled="disabled"
            @change="onChange"
        />
        <span class="mentor-toggle-track">
            <span class="mentor-toggle-thumb"></span>
        </span>
        <span class="mentor-toggle-label">
            {{ modelValue ? onLabel : offLabel }}
        </span>
    </label>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        modelValue: boolean;
        onLabel?: string;
        offLabel?: string;
        disabled?: boolean;
    }>(),
    {
        onLabel: '公開中',
        offLabel: '非公開',
        disabled: false,
    },
);

const emit = defineEmits<{
    'update:modelValue': [value: boolean];
}>();

const onChange = (event: Event): void => {
    emit('update:modelValue', (event.target as HTMLInputElement).checked);
};
</script>
