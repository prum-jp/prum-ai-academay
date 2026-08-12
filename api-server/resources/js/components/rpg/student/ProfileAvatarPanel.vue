<template>
    <AvatarUploader
        :avatar-url="avatarUrl"
        :editable="editable"
        :disabled="isUpdating"
        :error="error"
        @select="upload"
        @reset="reset(hasAvatar)"
        @invalid="setError"
    />
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { AdventurerProfile } from '@/types/profile/adventurer';
import { useAvatarUpload } from '@/composables/profile/useAvatarUpload';
import AvatarUploader from '@/components/rpg/student/AvatarUploader.vue';

const props = defineProps<{
    avatarUrl: string | null;
    editable: boolean;
}>();

const emit = defineEmits<{
    updated: [profile: AdventurerProfile];
}>();

const hasAvatar = computed(() => Boolean(props.avatarUrl));

const { error, isUpdating, setError, upload, reset } = useAvatarUpload((profile) => {
    emit('updated', profile);
});
</script>
