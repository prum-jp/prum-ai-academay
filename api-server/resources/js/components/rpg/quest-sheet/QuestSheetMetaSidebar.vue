<template>
    <aside class="quest-sheet-meta">
        <table class="quest-sheet-meta-table">
            <tbody>
                <tr v-for="row in rows" :key="row.label">
                    <th>{{ row.label }}</th>
                    <td>
                        <template v-if="row.lines?.length">
                            <p
                                v-for="(line, index) in row.lines"
                                :key="`${row.label}-${index}`"
                                class="quest-sheet-meta-line"
                            >
                                {{ line }}
                            </p>
                        </template>
                        <template v-else>{{ row.value }}</template>
                    </td>
                </tr>
            </tbody>
        </table>
    </aside>
</template>

<script setup lang="ts">
export interface QuestSheetMetaRow {
    label: string;
    value: string;
    lines?: string[];
}

defineProps<{
    rows: QuestSheetMetaRow[];
}>();
</script>
