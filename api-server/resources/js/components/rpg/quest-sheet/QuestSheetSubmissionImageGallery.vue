<template>
    <div class="quest-sheet-deliverable-image-grid">
        <figure
            v-for="image in images"
            :key="image.id"
            class="quest-sheet-deliverable-image-item"
        >
            <a
                v-if="image.url"
                class="quest-sheet-deliverable-image-link"
                :href="image.url"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    class="quest-sheet-deliverable-image"
                    :src="image.url"
                    :alt="questSubmissionMessages.previewImage"
                />
            </a>
            <button
                v-if="!isLocked"
                type="button"
                class="quest-sheet-deliverable-image-delete"
                :disabled="isBusy"
                :aria-label="questSubmissionMessages.deleteImageLabel"
                @click="$emit('delete', image.id)"
            >
                <i
                    v-if="deletingImageId === image.id"
                    class="fa-solid fa-spinner fa-spin"
                    aria-hidden="true"
                ></i>
                <i v-else class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </figure>
    </div>
</template>

<script setup lang="ts">
import { questSubmissionMessages } from '@/constants/quest/questSubmission';
import type { QuestSubmissionFile } from '@/types/quest/questSubmission';

defineProps<{
    images: QuestSubmissionFile[];
    isLocked: boolean;
    isBusy: boolean;
    deletingImageId: number | null;
}>();

defineEmits<{
    delete: [fileId: number];
}>();
</script>
