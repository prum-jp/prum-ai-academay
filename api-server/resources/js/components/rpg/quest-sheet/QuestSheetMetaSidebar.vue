<template>
    <aside class="quest-sheet-meta">
        <table class="quest-sheet-meta-table">
            <tbody>
                <tr v-for="row in rows" :key="row.label">
                    <th>{{ row.label }}</th>
                    <td>{{ row.value }}</td>
                </tr>
            </tbody>
        </table>
    </aside>
</template>

<script setup lang="ts">
export interface QuestSheetMetaRow {
    label: string;
    value: string;
}

defineProps<{
    rows: QuestSheetMetaRow[];
}>();
</script>
