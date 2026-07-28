<template>
    <div class="app-shell" :class="{ 'app-shell-centered': !isAuthenticated }">
        <nav v-if="isAuthenticated" class="app-nav">
            <div class="app-nav-links">
                <RouterLink v-if="isMentor" :to="{ name: 'mentor' }">管理画面</RouterLink>
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-quests' }">クエスト管理</RouterLink>
                <RouterLink v-if="isMentor" :to="{ name: 'mentor-tools' }">AIマスタ追加</RouterLink>
            </div>
            <span class="app-nav-user">{{ user?.name }}</span>
            <button class="app-nav-button" type="button" @click="handleLogout">ログアウト</button>
        </nav>
        <RouterView />
    </div>
</template>

<script setup lang="ts">
import { RouterLink, RouterView, useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';

const router = useRouter();
const { isAuthenticated, isMentor, user, logout } = useAuth();

const handleLogout = async (): Promise<void> => {
    await logout();
    await router.push({ name: 'login' });
};
</script>
