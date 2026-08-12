<template>
    <div class="equipment-slot">
        <div class="slot-badge">
            <i :class="icon"></i>
        </div>
        <div class="slot-body">
            <div class="input-group">
                <label>{{ label }}</label>
                <textarea
                    :value="modelValue"
                    rows="2"
                    :placeholder="placeholder"
                    :readonly="readonly"
                    @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
                    @blur="$emit('blur')"
                ></textarea>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        modelValue: string;
        icon: string;
        placeholder?: string;
        readonly?: boolean;
    }>(),
    {
        placeholder: '',
        readonly: false,
    },
);

defineEmits<{
    'update:modelValue': [value: string];
    blur: [];
}>();
</script>
