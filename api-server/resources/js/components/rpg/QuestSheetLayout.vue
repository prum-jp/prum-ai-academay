<template>
    <article class="quest-sheet">
        <div class="quest-sheet-heading">
            <table class="quest-sheet-heading-table">
                <tbody>
                    <tr>
                        <th>{{ questSheetConfig.questNoLabel }}</th>
                        <td class="quest-sheet-heading-number">{{ questNoDisplay }}</td>
                        <td class="quest-sheet-heading-title-cell">
                            <div class="quest-sheet-heading-quest-row">
                                <slot name="title">
                                    <h1 class="quest-sheet-heading-quest-title">{{ title }}</h1>
                                </slot>
                                <slot name="quest-status" />
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="quest-sheet-body">
            <slot name="meta">
                <QuestSheetMetaSidebar :rows="metaRows" />
            </slot>
            <slot />
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { QuestSheetMetaRow } from '@/components/rpg/QuestSheetMetaSidebar.vue';
import QuestSheetMetaSidebar from '@/components/rpg/QuestSheetMetaSidebar.vue';
import { questSheetConfig } from '@/constants/questSheet';

const props = withDefaults(
    defineProps<{
        title?: string;
        questNo?: number | null;
        metaRows?: QuestSheetMetaRow[];
    }>(),
    {
        title: '',
        questNo: null,
        metaRows: () => [],
    },
);

const questNoDisplay = computed(() =>
    props.questNo !== null && props.questNo !== undefined ? String(props.questNo) : '—',
);
</script>
