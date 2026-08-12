<template>
    <div class="quest-sheet-sections">
        <section
            v-for="section in sections"
            :key="section.title"
            class="quest-sheet-section"
        >
            <header class="quest-sheet-section-header">
                {{ section.title }}
            </header>
            <div class="quest-sheet-section-body">
                <template v-if="section.kind === 'deliverable'">
                    <p v-if="section.body.trim() === ''" class="quest-sheet-section-text">
                        {{ questSheetConfig.emptySection }}
                    </p>
                    <QuestSheetSectionText v-else :body="section.body" />

                    <QuestSheetDeliverableSubmit
                        v-if="questId !== null"
                        :quest-id="questId"
                        :submission="submission"
                        :is-locked="isLocked"
                        @saved="$emit('submission-saved', $event)"
                    />
                </template>
                <template v-else>
                    <p v-if="section.body.trim() === ''">{{ questSheetConfig.emptySection }}</p>
                    <QuestSheetSectionText v-else :body="section.body" />
                </template>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import type { QuestItem } from '@/types/quest/quest';
import type { QuestSubmission } from '@/types/quest/questSubmission';
import QuestSheetDeliverableSubmit from '@/components/rpg/quest-sheet/QuestSheetDeliverableSubmit.vue';
import QuestSheetSectionText from '@/components/rpg/quest-sheet/QuestSheetSectionText.vue';

export interface QuestSheetSectionItem {
    title: string;
    body: string;
    kind?: 'default' | 'deliverable';
}

withDefaults(
    defineProps<{
        sections: QuestSheetSectionItem[];
        questId?: number | null;
        submission?: QuestSubmission | null;
        isLocked?: boolean;
    }>(),
    {
        questId: null,
        submission: null,
        isLocked: false,
    },
);

defineEmits<{
    'submission-saved': [quest: QuestItem];
}>();
</script>
