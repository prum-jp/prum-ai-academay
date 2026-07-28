<template>
    <div class="input-group">
        <label>{{ label }}</label>
        <input
            :value="modelValue"
            type="text"
            :placeholder="placeholder"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
            @blur="$emit('blur')"
        />
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        modelValue: string;
        placeholder?: string;
    }>(),
    {
        placeholder: '',
    },
);

defineEmits<{
    'update:modelValue': [value: string];
    blur: [];
}>();
</script>
