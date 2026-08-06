<template>
    <MentorPanel :config="mentorQuestPageConfig">
        <MentorQuestBoard @notify="showNotice" />
        <ToastNotice :message="toastMessage" :show="showToast" />
    </MentorPanel>
</template>

<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    mentorQuestAdminMessages,
    mentorQuestPageConfig,
} from '@/constants/questAdmin';
import { questImportMessages } from '@/constants/questImport';
import MentorPanel from '@/components/rpg/MentorPanel.vue';
import MentorQuestBoard from '@/components/rpg/MentorQuestBoard.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const route = useRoute();
const router = useRouter();
const showToast = ref(false);
const toastMessage = ref('');

const showNotice = (message: string): void => {
    toastMessage.value = message;
    showToast.value = true;

    window.setTimeout(() => {
        showToast.value = false;
    }, 1200);
};

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
