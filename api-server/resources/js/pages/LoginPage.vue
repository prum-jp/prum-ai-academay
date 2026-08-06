<template>
    <div class="login-stage">
        <GameWindow
            title="ログイン"
            subtitle="PRUM AI ACADEMY / LOGIN"
            icon="fa-solid fa-key"
            single-column
        >
            <RpgCard title="ログイン情報" icon="fa-solid fa-door-open">
                <form class="login-form" @submit.prevent="handleLogin">
                    <div class="input-group">
                        <label for="login-email">メールアドレス</label>
                        <input
                            id="login-email"
                            v-model="email"
                            type="email"
                            autocomplete="username"
                            required
                            placeholder="メールアドレス"
                        />
                    </div>

                    <div class="input-group">
                        <label for="login-password">パスワード</label>
                        <input
                            id="login-password"
                            v-model="password"
                            type="password"
                            autocomplete="current-password"
                            required
                            placeholder="パスワード"
                        />
                    </div>

                    <p v-if="errorMessage" class="login-error">{{ errorMessage }}</p>

                    <div class="action-area">
                        <RpgButton type="submit" icon="fa-solid fa-right-to-bracket" :disabled="isSubmitting">
                            {{ isSubmitting ? '確認中...' : 'ログイン' }}
                        </RpgButton>
                    </div>
                </form>
            </RpgCard>
        </GameWindow>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { extractApiErrorMessage } from '@/utils/extractApiErrorMessage';
import GameWindow from '@/components/rpg/GameWindow.vue';
import RpgCard from '@/components/rpg/RpgCard.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';

const router = useRouter();
const { login, homePathFor } = useAuth();

const email = ref('');
const password = ref('');
const errorMessage = ref('');
const isSubmitting = ref(false);

const handleLogin = async (): Promise<void> => {
    errorMessage.value = '';
    isSubmitting.value = true;

    try {
        const user = await login(email.value, password.value);
        await router.push(homePathFor(user));
    } catch (error: unknown) {
        errorMessage.value = extractApiErrorMessage(
            error,
            'email',
            'ログインに失敗しました。',
        );
    } finally {
        isSubmitting.value = false;
    }
};
</script>
