<template>
    <div v-if="submission" class="quest-sheet-deliverable-preview">
        <p class="quest-sheet-deliverable-saved">
            <span class="quest-sheet-deliverable-saved-label">
                {{ questSubmissionMessages.savedLabel }}
            </span>
            <span class="quest-sheet-deliverable-saved-type">
                （{{ questSubmissionTypeLabels[submission.type] }}）
            </span>
        </p>

        <template v-if="submission.type === 'image' && images.length > 0">
            <p class="quest-sheet-deliverable-saved-images-label">
                {{ questSubmissionMessages.savedImagesLabel }}
            </p>
            <QuestSheetSubmissionImageGallery
                :images="images"
                :is-locked="isLocked"
                :is-busy="isBusy"
                :deleting-image-id="deletingImageId"
                @delete="$emit('delete-image', $event)"
            />
        </template>

        <a
            v-else-if="submission.type === 'link' && submission.url"
            class="quest-sheet-deliverable-link"
            :href="submission.url"
            target="_blank"
            rel="noopener noreferrer"
        >
            {{ questSubmissionMessages.openLinkLabel }}
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>

        <a
            v-else-if="submission.url && submission.type !== 'text'"
            class="quest-sheet-deliverable-link"
            :href="submission.url"
            target="_blank"
            rel="noopener noreferrer"
        >
            {{ questSubmissionMessages.openFileLabel }}
            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
        </a>

        <video
            v-if="submission.type === 'video' && submission.url"
            class="quest-sheet-deliverable-media"
            :src="submission.url"
            controls
        />

        <audio
            v-if="submission.type === 'audio' && submission.url"
            class="quest-sheet-deliverable-media"
            :src="submission.url"
            controls
        />

        <p
            v-if="submission.type === 'text' && submission.text"
            class="quest-sheet-deliverable-text"
        >
            {{ submission.text }}
        </p>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import QuestSheetSubmissionImageGallery from '@/components/rpg/quest-sheet/QuestSheetSubmissionImageGallery.vue';
import {
    questSubmissionMessages,
    questSubmissionTypeLabels,
} from '@/constants/quest/questSubmission';
import type { QuestSubmission } from '@/types/quest/questSubmission';

const props = defineProps<{
    submission: QuestSubmission | null;
    isLocked: boolean;
    isBusy: boolean;
    deletingImageId: number | null;
}>();

defineEmits<{
    'delete-image': [fileId: number];
}>();

const images = computed(() => props.submission?.files ?? []);
</script>
