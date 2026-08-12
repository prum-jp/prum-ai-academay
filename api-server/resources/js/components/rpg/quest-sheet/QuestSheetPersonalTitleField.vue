<template>
    <input
        :id="id"
        :value="modelValue"
        class="quest-sheet-create-meta-input quest-sheet-heading-title-input"
        type="text"
        :required="required"
        :readonly="readonly"
        maxlength="255"
        :placeholder="placeholder"
        :disabled="disabled"
        @input="onInput"
    />
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        id?: string;
        required?: boolean;
        readonly?: boolean;
        placeholder?: string;
        disabled?: boolean;
    }>(),
    {
        required: false,
        readonly: false,
        placeholder: '',
        disabled: false,
    },
);

const modelValue = defineModel<string>({ required: true });

const onInput = (event: Event): void => {
    modelValue.value = (event.target as HTMLInputElement).value;
};
</script>
