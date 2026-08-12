<template>
    <div class="app-shell" :class="{ 'app-shell-centered': !isAuthenticated }">
        <nav v-if="isAuthenticated" class="app-nav">
            <div class="app-nav-links">
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-quests' }">クエスト管理</RouterLink>
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-notifications' }">
                    {{ mentorNotificationsConfig.navLabel }}
                    <span v-if="reviewRequestCount > 0" class="app-nav-badge">
                        {{ reviewRequestCount }}
                    </span>
                </RouterLink>
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-quest-master' }">クエストマスタ</RouterLink>
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-tools' }">AIマスタ追加</RouterLink>
            </div>
            <div class="app-nav-user-wrap">
                <span class="app-nav-user">{{ user?.name }}</span>
                <StudentNotificationBell v-if="isStudent" />
            </div>
            <button class="app-nav-button" type="button" @click="handleLogout">ログアウト</button>
        </nav>
        <RouterView />
    </div>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { mentorNotificationsConfig } from '@/constants/mentor/mentorNotifications';
import { useAuth } from '@/composables/shared/useAuth';
import { useMentorReviewRequests } from '@/composables/mentor/useMentorReviewRequests';
import StudentNotificationBell from '@/components/rpg/student/StudentNotificationBell.vue';

const router = useRouter();
const { isAuthenticated, isMentor, isStudent, user, logout } = useAuth();
const { total: reviewRequestCount, refresh: refreshReviewRequests } = useMentorReviewRequests();

const handleLogout = async (): Promise<void> => {
    await logout();
    await router.push({ name: 'login' });
};

const loadReviewRequestCount = (): void => {
    if (isMentor.value) {
        void refreshReviewRequests();
    }
};

onMounted(() => {
    loadReviewRequestCount();
});

watch(isMentor, () => {
    loadReviewRequestCount();
});

watch(
    () => router.currentRoute.value.name,
    () => {
        loadReviewRequestCount();
    },
);
</script>
