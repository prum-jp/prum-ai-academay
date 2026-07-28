<template>
    <article class="quest-import-row" :class="{ 'has-error': hasError }">
        <div class="quest-import-row-head">
            <div class="quest-import-row-badges">
                <span class="quest-import-badge">{{ kindLabel }}</span>
                <span class="quest-import-badge" :class="`is-${item.action}`">
                    {{ actionLabel }}
                </span>
            </div>
            <div class="quest-import-row-actions">
                <MentorPublishToggle
                    :model-value="item.isPublished"
                    :on-label="questImportPublishLabels.on"
                    :off-label="questImportPublishLabels.off"
                    @update:model-value="onPublishChange"
                />
                <button type="button" class="quest-import-link" @click="expanded = !expanded">
                    {{
                        expanded
                            ? questImportFieldLabels.collapseLabel
                            : questImportFieldLabels.expandLabel
                    }}
                </button>
                <button type="button" class="quest-import-link is-danger" @click="emit('remove')">
                    削除
                </button>
            </div>
        </div>

        <div class="quest-import-row-summary">
            <strong>{{ item.title }}</strong>
            <span v-if="item.kind === 'child_quest' && item.unitTitle" class="quest-import-row-sub">
                {{ questImportFieldLabels.unitTitle }}: {{ item.unitTitle }}
            </span>
        </div>

        <ul v-if="hasError" class="quest-import-error-list">
            <li v-for="(message, index) in item.errors" :key="index">{{ message }}</li>
        </ul>

        <div v-if="expanded" class="quest-import-row-form">
            <div class="input-group">
                <label>
                    {{
                        item.kind === 'personal_unit'
                            ? questImportFieldLabels.unitTitle
                            : questImportFieldLabels.title
                    }}
                </label>
                <input v-model="item.title" type="text" />
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.unitTitle }}</label>
                <input v-model="item.unitTitle" type="text" />
            </div>

            <div v-if="item.kind === 'personal_unit' && childQuestTitles.length > 0" class="input-group">
                <label>{{ questImportFieldLabels.childQuests }}</label>
                <ul class="quest-import-unit-quest-list is-form">
                    <li v-for="(questTitle, index) in childQuestTitles" :key="index">{{ questTitle }}</li>
                </ul>
            </div>

            <div class="input-group">
                <label>{{ questImportFieldLabels.description }}</label>
                <textarea v-model="item.description" rows="4"></textarea>
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.clearCondition }}</label>
                <textarea v-model="item.clearCondition" rows="3"></textarea>
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.toolCode }}</label>
                <input v-model="item.toolCode" type="text" placeholder="例: Gemini" />
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.estimatedDuration }}</label>
                <input v-model="item.estimatedDuration" type="text" placeholder="例: 20分" />
            </div>

            <div v-if="item.difficulty" class="quest-import-row-meta">
                <div class="input-group">
                    <label>{{ questImportFieldLabels.difficulty }}</label>
                    <input :value="item.difficulty" type="text" readonly class="is-readonly" />
                    <p class="quest-import-row-note">※ 難度は DB 未対応のため取り込みません</p>
                </div>
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import type { QuestImportAction, QuestImportItem } from '@/types/questImport';
import {
    questImportActionLabels,
    questImportFieldLabels,
    questImportKindLabels,
    questImportPublishLabels,
} from '@/constants/questImport';
import { applyPublishChange } from '@/utils/questImport/publish';
import { sortChildQuests } from '@/utils/questImport/sortComparators';
import MentorPublishToggle from '@/components/rpg/MentorPublishToggle.vue';

const item = defineModel<QuestImportItem>({ required: true });

const props = defineProps<{
    allItems: QuestImportItem[];
}>();

const emit = defineEmits<{
    remove: [];
    syncUnitPublish: [unitTitle: string, isPublished: boolean];
}>();

const expanded = ref(false);

const kindLabel = computed(() => questImportKindLabels[item.value.kind] ?? item.value.kind);
const hasError = computed(() => (item.value.errors?.length ?? 0) > 0);
const actionLabel = computed((): string => {
    const action: QuestImportAction = item.value.action ?? 'create';
    return questImportActionLabels[action];
});

const onPublishChange = (value: boolean): void => {
    if (item.value.kind === 'personal_unit') {
        emit('syncUnitPublish', item.value.title, value);
        return;
    }

    Object.assign(item.value, applyPublishChange(item.value, value));
};

const childQuestTitles = computed((): string[] => {
    if (item.value.kind !== 'personal_unit') {
        return [];
    }

    return sortChildQuests(
        props.allItems.filter(
            (row) => row.kind === 'child_quest' && row.unitTitle === item.value.title,
        ),
    ).map((row) => row.title);
});
</script>
