<template>
    <MentorPanel :config="mentorNotificationsPageConfig">
        <MentorNotificationTable
            :items="items"
            :is-loading="isLoading"
            :error="error"
            :disabled="isOpening"
            @open="onOpen"
        />
        <p v-if="openError" class="login-error">{{ openError }}</p>
    </MentorPanel>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import { selectMentorStudent } from '@/api/mentor/mentor';
import { mentorNotificationsPageConfig } from '@/constants/mentor/mentorNotifications';
import { mentorStudentMessages } from '@/constants/mentor/mentor';
import { useMentorReviewRequests } from '@/composables/mentor/useMentorReviewRequests';
import type { MentorReviewRequestItem } from '@/types/mentor/mentorNotifications';
import { extractApiErrorMessage } from '@/utils/shared/extractApiErrorMessage';
import MentorNotificationTable from '@/components/rpg/mentor/MentorNotificationTable.vue';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';

const router = useRouter();
const { items, isLoading, error, refresh } = useMentorReviewRequests();
const isOpening = ref(false);
const openError = ref('');

const onOpen = async (item: MentorReviewRequestItem): Promise<void> => {
    if (isOpening.value) {
        return;
    }

    isOpening.value = true;
    openError.value = '';

    try {
        await selectMentorStudent(item.studentId);
        await router.push({
            name: 'student-quest-detail',
            params: { questId: item.questId },
        });
    } catch (caughtError: unknown) {
        openError.value = extractApiErrorMessage(
            caughtError,
            'studentId',
            mentorStudentMessages.selectFailed,
        );
    } finally {
        isOpening.value = false;
    }
};

onMounted(() => {
    void refresh();
});
</script>
