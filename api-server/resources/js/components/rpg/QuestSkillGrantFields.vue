<template>
    <div
        class="quest-skill-grant-fields"
        :class="{ 'is-meta-table': variant === 'metaTable' }"
    >
        <p v-if="variant !== 'metaTable'" class="quest-skill-grant-lead">{{ leadLabel }}</p>
        <div class="quest-skill-grant-options">
            <label
                v-for="skill in skillDefinitions"
                :key="skill.key"
                class="quest-skill-grant-option"
                :class="{ 'is-checked': model.includes(skill.key) }"
            >
                <input
                    type="checkbox"
                    class="quest-skill-grant-checkbox"
                    :checked="model.includes(skill.key)"
                    :disabled="disabled"
                    @change="toggle(skill.key, ($event.target as HTMLInputElement).checked)"
                />
                <i :class="skill.icon" aria-hidden="true"></i>
                <span>{{ skill.label }}</span>
            </label>
        </div>
    </div>
</template>

<script setup lang="ts">
import { skillDefinitions, type SkillKey } from '@/constants/skills';

withDefaults(
    defineProps<{
        disabled?: boolean;
        leadLabel?: string;
        variant?: 'default' | 'metaTable';
    }>(),
    {
        disabled: false,
        leadLabel: '完了時に付与するスキル（複数選択可・各1ポイント）',
        variant: 'default',
    },
);

const model = defineModel<SkillKey[]>({ required: true });

const toggle = (skill: SkillKey, checked: boolean): void => {
    if (checked) {
        if (!model.value.includes(skill)) {
            model.value = [...model.value, skill];
        }
        return;
    }

    model.value = model.value.filter((item) => item !== skill);
};
</script>
