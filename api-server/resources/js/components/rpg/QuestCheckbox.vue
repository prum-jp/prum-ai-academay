<template>
    <label
        class="quest-check"
        :class="{ 'is-indeterminate': indeterminate }"
        @click.stop
    >
        <input
            ref="checkboxRef"
            type="checkbox"
            :checked="checked"
            :disabled="disabled"
            @change="$emit('toggle')"
        />
        <span class="quest-check-box" aria-hidden="true">
            <i class="fa-solid fa-check"></i>
        </span>
    </label>
</template>

<script setup lang="ts">
import { onUpdated, ref, toRef, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        checked: boolean;
        indeterminate?: boolean;
        disabled?: boolean;
    }>(),
    {
        indeterminate: false,
        disabled: false,
    },
);

defineEmits<{
    toggle: [];
}>();

const checkboxRef = ref<HTMLInputElement | null>(null);
const indeterminate = toRef(props, 'indeterminate');

const syncIndeterminate = (): void => {
    if (!checkboxRef.value) {
        return;
    }

    checkboxRef.value.indeterminate = indeterminate.value;
};

watch(indeterminate, syncIndeterminate, { immediate: true });
onUpdated(syncIndeterminate);
</script>
