<template>
    <div class="mentor-tool-select">
        <p v-if="tools.length === 0" class="mentor-tool-select-empty">
            {{ mentorToolSelectConfig.empty }}
            <RouterLink :to="{ name: 'mentor-tools' }">{{ mentorToolSelectConfig.addLinkLabel }}</RouterLink>
        </p>

        <ul v-else class="mentor-tool-select-list">
            <li v-for="tool in tools" :key="tool.id">
                <label class="mentor-tool-select-item">
                    <input
                        type="checkbox"
                        :value="tool.id"
                        :checked="modelValue.includes(tool.id)"
                        :disabled="disabled"
                        @change="onToggle(tool.id, ($event.target as HTMLInputElement).checked)"
                    />
                    <span>{{ tool.name }}</span>
                </label>
            </li>
        </ul>
    </div>
</template>

<script setup lang="ts">
import { RouterLink } from 'vue-router';
import type { MentorTool } from '@/types/questAdmin';
import { mentorToolSelectConfig } from '@/constants/toolAdmin';

const modelValue = defineModel<number[]>({ required: true });

withDefaults(
    defineProps<{
        tools: MentorTool[];
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    },
);

const onToggle = (toolId: number, checked: boolean): void => {
    if (checked) {
        if (!modelValue.value.includes(toolId)) {
            modelValue.value = [...modelValue.value, toolId];
        }

        return;
    }

    modelValue.value = modelValue.value.filter((id) => id !== toolId);
};
</script>

<style scoped>
.mentor-tool-select-empty {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.mentor-tool-select-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    gap: 8px;
}

.mentor-tool-select-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
}

.mentor-tool-select-item input {
    margin: 0;
}
</style>
