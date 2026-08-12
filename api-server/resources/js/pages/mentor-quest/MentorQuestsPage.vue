<template>
    <MentorPanel :config="mentorQuestPageConfig">
        <MentorQuestBoard @notify="showNotice" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    mentorQuestAdminMessages,
    mentorQuestPageConfig,
} from '@/constants/mentor-quest/questAdmin';
import { questImportMessages } from '@/constants/mentor-quest/questImport';
import { useToastNotice } from '@/composables/shared/useToastNotice';
import MentorPanel from '@/components/rpg/mentor/MentorPanel.vue';
import MentorQuestBoard from '@/components/rpg/mentor-quest/MentorQuestBoard.vue';
import ToastNotice from '@/components/rpg/shared/ToastNotice.vue';

const route = useRoute();
const router = useRouter();
const { showToast, toastMessage, showNotice } = useToastNotice();

const resolveNoticeMessage = (notice: string): string | null => {
    if (notice === 'unit-created') {
        return mentorQuestAdminMessages.createUnitSuccessToast;
    }

    if (notice === 'quest-created') {
        return mentorQuestAdminMessages.createQuestSuccessToast;
    }

    if (notice === 'imported') {
        return questImportMessages.applySuccess;
    }

    return null;
};

const showNoticeFromQuery = (): void => {
    const notice = route.query.notice;
    if (typeof notice !== 'string') {
        return;
    }

    const message = resolveNoticeMessage(notice);
    if (!message) {
        return;
    }

    showNotice(message);
    void router.replace({ name: 'mentor-quests' });
};

onMounted(() => {
    showNoticeFromQuery();
});

watch(
    () => route.query.notice,
    () => {
        showNoticeFromQuery();
    },
);
</script>
