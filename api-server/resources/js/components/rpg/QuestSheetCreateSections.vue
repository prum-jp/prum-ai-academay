<template>
    <div class="quest-sheet-sections">
        <section
            v-for="section in sectionDefinitions"
            :key="section.key"
            class="quest-sheet-section"
        >
            <header class="quest-sheet-section-header">
                {{ section.title }}
            </header>
            <div class="quest-sheet-section-body">
                <textarea
                    :id="`quest-create-section-${section.key}`"
                    v-model="model[section.key]"
                    class="quest-sheet-create-section-input"
                    rows="5"
                    maxlength="4000"
                    :placeholder="section.placeholder"
                    :disabled="disabled"
                />
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { questSheetConfig } from '@/constants/questSheet';
import type { QuestDescriptionSections } from '@/utils/questDescriptionSections';

const model = defineModel<QuestDescriptionSections>({ required: true });

withDefaults(
    defineProps<{
        disabled?: boolean;
    }>(),
    {
        disabled: false,
    },
);

const sectionDefinitions = [
    {
        key: 'overview' as const,
        title: questSheetConfig.sections.overview,
        placeholder: 'クエストの概要を入力',
    },
    {
        key: 'purpose' as const,
        title: questSheetConfig.sections.purpose,
        placeholder: 'なぜこのクエストに取り組むのかを入力',
    },
    {
        key: 'deliverable' as const,
        title: questSheetConfig.sections.deliverable,
        placeholder: '提出物の内容や形式を入力',
    },
    {
        key: 'completionCondition' as const,
        title: questSheetConfig.sections.completionCondition,
        placeholder: '完了条件を入力',
    },
];
</script>
