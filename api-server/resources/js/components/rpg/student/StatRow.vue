<template>
    <div class="stat-row">
        <div class="stat-name">
            <i :class="icon"></i>
            {{ label }}
        </div>
        <div class="stat-controls">
            <slot name="controls">
                <template v-if="editable">
                    <button
                        class="btn-circle"
                        type="button"
                        :disabled="value <= min"
                        @click="$emit('decrease')"
                    >
                        -
                    </button>
                    <span class="stat-val">{{ value }}</span>
                    <button
                        class="btn-circle"
                        type="button"
                        :disabled="value >= max"
                        @click="$emit('increase')"
                    >
                        +
                    </button>
                </template>
                <span v-else class="stat-val">{{ value }}</span>
            </slot>
        </div>
    </div>
</template>

<script setup lang="ts">
withDefaults(
    defineProps<{
        label: string;
        icon: string;
        value: number;
        editable?: boolean;
        min?: number;
        max?: number;
    }>(),
    {
        editable: false,
        min: 0,
        max: 10,
    },
);

defineEmits<{
    increase: [];
    decrease: [];
}>();
</script>
