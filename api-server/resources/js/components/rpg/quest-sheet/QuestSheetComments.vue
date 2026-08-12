<template>
    <section class="quest-sheet-section quest-sheet-comments">
        <header class="quest-sheet-section-header">
            {{ questCommentsConfig.title }}
        </header>

        <div class="quest-sheet-section-body quest-sheet-comments-body">
            <p v-if="isLoading" class="quest-sheet-comments-empty">{{ questSheetConfig.loading }}</p>

            <p v-else-if="loadError" class="quest-sheet-comments-error">{{ loadError }}</p>

            <template v-else>
                <p v-if="items.length === 0" class="quest-sheet-comments-empty">
                    {{ questCommentsConfig.empty }}
                </p>

                <ul v-else class="quest-sheet-comment-list">
                    <li
                        v-for="item in items"
                        :key="item.id"
                        class="quest-sheet-comment-item"
                        :class="{
                            'is-own': item.isOwn,
                            'is-activity': isQuestActivity(item),
                        }"
                    >
                        <div
                            class="quest-sheet-comment-avatar"
                            :class="{ 'is-activity': isQuestActivity(item) }"
                            aria-hidden="true"
                        >
                            <template v-if="isQuestActivity(item)">
                                <i :class="getQuestActivityIcon(item)"></i>
                            </template>
                            <template v-else>
                                <img
                                    v-if="item.authorAvatarUrl"
                                    :src="item.authorAvatarUrl"
                                    :alt="item.authorName"
                                />
                                <span v-else>{{ getDisplayInitial(item.authorName) }}</span>
                            </template>
                        </div>

                        <div class="quest-sheet-comment-content">
                            <div class="quest-sheet-comment-meta">
                                <strong class="quest-sheet-comment-author">
                                    {{ item.authorName }}
                                </strong>
                                <span
                                    v-if="item.authorRole === 'mentor'"
                                    class="quest-sheet-comment-role"
                                >
                                    {{ questCommentsConfig.roleLabels.mentor }}
                                </span>
                                <time class="quest-sheet-comment-time">
                                    {{ formatDateTime(item.createdAt) }}
                                </time>
                            </div>

                            <p
                                class="quest-sheet-comment-body"
                                :class="{ 'is-activity': isQuestActivity(item) }"
                            >
                                {{ isQuestActivity(item) ? formatQuestActivityText(item) : item.body }}
                            </p>

                            <p
                                v-if="getQuestActivityPreviewText(item)"
                                class="quest-sheet-comment-activity-preview"
                            >
                                {{ getQuestActivityPreviewText(item) }}
                            </p>

                            <a
                                v-if="getQuestActivityLink(item)"
                                class="quest-sheet-comment-activity-link"
                                :href="getQuestActivityLink(item) ?? undefined"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ questCommentsConfig.activity.openSubmissionLink }}
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>
                        </div>
                    </li>
                </ul>

                <form class="quest-sheet-comment-composer" @submit.prevent="onSubmit">
                    <div class="quest-sheet-comment-avatar" aria-hidden="true">
                        <span>{{ currentUserInitial }}</span>
                    </div>

                    <div class="quest-sheet-comment-composer-main">
                        <textarea
                            v-model="draft"
                            class="quest-sheet-comment-input"
                            rows="3"
                            :placeholder="questCommentsConfig.placeholder"
                            :disabled="isComposerDisabled"
                        />

                        <div class="quest-sheet-comment-composer-actions">
                            <p v-if="submitError" class="quest-sheet-comments-error">
                                {{ submitError }}
                            </p>

                            <button
                                type="submit"
                                class="btn-rpg quest-sheet-comment-submit"
                                :disabled="isComposerDisabled || draft.trim() === ''"
                            >
                                <i
                                    v-if="isSubmitting"
                                    class="fa-solid fa-spinner fa-spin"
                                    aria-hidden="true"
                                ></i>
                                {{
                                    isSubmitting
                                        ? questCommentsConfig.submittingLabel
                                        : questCommentsConfig.submitLabel
                                }}
                            </button>
                        </div>
                    </div>
                </form>
            </template>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { questCommentsConfig } from '@/constants/quest/questComments';
import { questSheetConfig } from '@/constants/quest-sheet/questSheet';
import { useAuth } from '@/composables/shared/useAuth';
import type { QuestComment } from '@/types/quest/questComment';
import { formatDateTime } from '@/utils/shared/formatDateTime';
import { getDisplayInitial } from '@/utils/shared/displayInitial';
import {
    formatQuestActivityText,
    getQuestActivityIcon,
    getQuestActivityLink,
    getQuestActivityPreviewText,
    isQuestActivity,
} from '@/utils/quest/questActivityDisplay';

const props = defineProps<{
    items: QuestComment[];
    isLoading: boolean;
    loadError: string;
    isLocked?: boolean;
    isMentor?: boolean;
    onPost: (body: string) => Promise<QuestComment | null>;
}>();

const { user } = useAuth();
const draft = ref('');
const isSubmitting = ref(false);
const submitError = ref('');

const currentUserInitial = computed(() => getDisplayInitial(user.value?.name ?? ''));

const isComposerDisabled = computed(
    () => isSubmitting.value || (props.isLocked === true && props.isMentor !== true),
);

const onSubmit = async (): Promise<void> => {
    const body = draft.value.trim();
    if (body === '' || isComposerDisabled.value) {
        return;
    }

    isSubmitting.value = true;
    submitError.value = '';

    const created = await props.onPost(body);
    if (created) {
        draft.value = '';
    } else {
        submitError.value = questCommentsConfig.submitFailed;
    }

    isSubmitting.value = false;
};
</script>
