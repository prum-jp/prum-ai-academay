<template>
    <RpgCard title="基本情報" icon="fa-solid fa-user-ninja">
        <ProfileAvatarPanel
            :avatar-url="profile.avatarUrl"
            :editable="editable"
            @updated="$emit('profile-updated', $event)"
        />
        <EditableField
            v-model="profile.name"
            label="受講者名"
            placeholder="例：山田太郎"
            :readonly="!editable"
            @blur="$emit('persist')"
        />
        <EditableField
            v-model="profile.background"
            label="前職 / バックグラウンド"
            placeholder="例：住宅営業職（交渉が得意）"
            :readonly="!editable"
            @blur="$emit('persist')"
        />
        <EditableField
            v-model="profile.hobby"
            label="趣味 / ハマっていること"
            placeholder="例：サウナ / カフェ巡り"
            :readonly="!editable"
            @blur="$emit('persist')"
        />
        <p v-if="saveStatusLabel" class="save-status" :class="`is-${saveStatus}`">
            {{ saveStatusLabel }}
        </p>
    </RpgCard>
</template>

<script setup lang="ts">
import type { AdventurerProfile } from '@/types/profile/adventurer';
import RpgCard from '@/components/rpg/shared/RpgCard.vue';
import ProfileAvatarPanel from '@/components/rpg/student/ProfileAvatarPanel.vue';
import EditableField from '@/components/rpg/shared/EditableField.vue';

defineProps<{
    profile: AdventurerProfile;
    editable: boolean;
    saveStatus: 'idle' | 'saving' | 'error';
    saveStatusLabel: string;
}>();

defineEmits<{
    persist: [];
    'profile-updated': [profile: AdventurerProfile];
}>();
</script>
