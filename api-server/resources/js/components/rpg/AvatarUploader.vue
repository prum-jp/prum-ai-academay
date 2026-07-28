<template>
    <div class="avatar-uploader">
        <div class="avatar-preview" :class="{ 'has-image': Boolean(displayUrl) }">
            <img v-if="displayUrl" :src="displayUrl" alt="プロフィール画像" />
            <span v-else>{{ avatarConfig.placeholderLabel }}</span>
        </div>

        <div class="avatar-uploader-body">
            <div v-if="editable" class="avatar-actions">
                <button
                    type="button"
                    class="avatar-btn avatar-btn-primary"
                    :disabled="disabled"
                    @click="openFilePicker"
                >
                    <i class="fa-solid fa-camera"></i>
                    写真を選択
                </button>
                <button
                    type="button"
                    class="avatar-btn avatar-btn-secondary"
                    :disabled="disabled || !hasAvatar"
                    @click="handleReset"
                >
                    リセット
                </button>
            </div>
            <p class="avatar-hint">{{ avatarConfig.hint }}</p>
            <p v-if="error" class="login-error avatar-error">{{ error }}</p>
        </div>

        <input
            ref="fileInput"
            type="file"
            class="avatar-file-input"
            :accept="AVATAR_ACCEPT"
            @change="onFileChange"
        />
    </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { AVATAR_ACCEPT, avatarConfig, validateAvatarFile } from '@/constants/avatar';

const props = withDefaults(
    defineProps<{
        avatarUrl?: string | null;
        editable?: boolean;
        disabled?: boolean;
        error?: string;
    }>(),
    {
        avatarUrl: null,
        editable: false,
        disabled: false,
        error: '',
    },
);

const emit = defineEmits<{
    select: [file: File];
    reset: [];
    invalid: [message: string];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const localPreviewUrl = ref<string | null>(null);

const hasAvatar = computed(() => Boolean(props.avatarUrl || localPreviewUrl.value));
const displayUrl = computed(() => localPreviewUrl.value || props.avatarUrl || null);

const clearLocalPreview = (): void => {
    if (localPreviewUrl.value) {
        URL.revokeObjectURL(localPreviewUrl.value);
        localPreviewUrl.value = null;
    }
};

watch(
    () => props.avatarUrl,
    () => {
        clearLocalPreview();
    },
);

const openFilePicker = (): void => {
    if (props.disabled) {
        return;
    }

    fileInput.value?.click();
};

const onFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        return;
    }

    const validationError = validateAvatarFile(file);
    input.value = '';

    if (validationError) {
        clearLocalPreview();
        emit('invalid', validationError);
        return;
    }

    clearLocalPreview();
    localPreviewUrl.value = URL.createObjectURL(file);
    emit('select', file);
};

const handleReset = (): void => {
    clearLocalPreview();
    emit('reset');
};

defineExpose({
    clearLocalPreview,
});
</script>
