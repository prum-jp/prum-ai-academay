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

            <div v-if="item.kind === 'child_quest'" class="quest-import-checkbox-group">
                <label class="quest-import-toggle">
                    <input v-model="item.isRequired" type="checkbox" />
                    {{ questImportFieldLabels.isRequired }}
                </label>
            </div>

            <template v-if="item.kind === 'child_quest'">
                <div class="input-group">
                    <label>{{ questImportFieldLabels.overview }}</label>
                    <textarea v-model="item.overview" rows="4"></textarea>
                    <div v-if="(item.overview ?? '').trim()" class="quest-import-field-preview">
                        <QuestSheetSectionText :body="item.overview ?? ''" />
                    </div>
                </div>

                <div class="input-group">
                    <label>{{ questImportFieldLabels.purpose }}</label>
                    <textarea v-model="item.purpose" rows="3"></textarea>
                    <div v-if="(item.purpose ?? '').trim()" class="quest-import-field-preview">
                        <QuestSheetSectionText :body="item.purpose ?? ''" />
                    </div>
                </div>

                <div class="input-group">
                    <label>{{ questImportFieldLabels.deliverable }}</label>
                    <textarea v-model="item.deliverable" rows="3"></textarea>
                    <div v-if="(item.deliverable ?? '').trim()" class="quest-import-field-preview">
                        <QuestSheetSectionText :body="item.deliverable ?? ''" />
                    </div>
                </div>

                <div class="input-group">
                    <label>{{ questImportFieldLabels.completionCondition }}</label>
                    <textarea v-model="item.completionCondition" rows="3"></textarea>
                    <div
                        v-if="(item.completionCondition ?? '').trim()"
                        class="quest-import-field-preview"
                    >
                        <QuestSheetSectionText :body="item.completionCondition ?? ''" />
                    </div>
                </div>
            </template>

            <div
                v-else-if="item.kind === 'team_quest' || item.kind === 'special_quest'"
                class="input-group"
            >
                <label>{{ questImportFieldLabels.description }}</label>
                <textarea v-model="item.description" rows="4"></textarea>
                <div v-if="(item.description ?? '').trim()" class="quest-import-field-preview">
                    <QuestSheetSectionText :body="item.description ?? ''" />
                </div>
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.toolCode }}</label>
                <input v-model="item.toolCode" type="text" placeholder="例: Gemini" />
            </div>

            <div v-if="item.kind === 'child_quest'" class="input-group">
                <label>{{ questImportFieldLabels.questTier }}</label>
                <select v-model="childQuestTier" class="quest-sheet-create-meta-input">
                    <option
                        v-for="option in QUEST_TIER_OPTIONS"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}（{{ option.requirement }}）
                    </option>
                </select>
            </div>

            <div
                v-if="item.kind === 'child_quest' || item.kind === 'team_quest' || item.kind === 'special_quest'"
                class="quest-import-skill-grants"
            >
                <QuestSkillGrantFields v-model="item.skillGrants" />
            </div>

            <div v-if="item.difficulty" class="quest-import-row-meta">
                <div class="input-group">
                    <label>{{ questImportFieldLabels.difficulty }}</label>
                    <input
                        :value="formatDifficulty(item.difficulty)"
                        type="text"
                        readonly
                        class="is-readonly"
                    />
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
} from '@/constants/questImport';
import { DEFAULT_QUEST_TIER, QUEST_TIER_OPTIONS } from '@/constants/questTier';
import { sortChildQuests } from '@/utils/questImport/sortComparators';
import { formatQuestDifficultyStars } from '@/utils/questDifficulty';
import QuestSheetSectionText from '@/components/rpg/QuestSheetSectionText.vue';
import QuestSkillGrantFields from '@/components/rpg/QuestSkillGrantFields.vue';

const item = defineModel<QuestImportItem>({ required: true });

const props = defineProps<{
    allItems: QuestImportItem[];
}>();

const emit = defineEmits<{
    remove: [];
}>();

const expanded = ref(false);

const childQuestTier = computed({
    get: () => item.value.questTier ?? DEFAULT_QUEST_TIER,
    set: (value) => {
        item.value.questTier = value;
    },
});

const kindLabel = computed(() => questImportKindLabels[item.value.kind] ?? item.value.kind);
const hasError = computed(() => (item.value.errors?.length ?? 0) > 0);
const actionLabel = computed((): string => {
    const action: QuestImportAction = item.value.action ?? 'create';
    return questImportActionLabels[action];
});

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

const formatDifficulty = (difficulty: number | undefined): string =>
    formatQuestDifficultyStars(difficulty);
</script>
