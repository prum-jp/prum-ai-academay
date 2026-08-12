import { computed, onMounted, ref } from 'vue';
import type { AdventurerProfile } from '@/types/profile/adventurer';
import { fetchStudentProfile, updateStudentProfile } from '@/api/profile/profile';
import { useAuth } from '@/composables/shared/useAuth';
import { useAdventurerCardPreview } from '@/composables/profile/useAdventurerCardPreview';

const profile = ref<AdventurerProfile | null>(null);
const isLoading = ref(true);
const loadError = ref('');
const saveStatus = ref<'idle' | 'saving' | 'error'>('idle');

const { user, isStudent } = useAuth();
const { slackPreview, showToast, toastMessage, copyAdventurerCard } = useAdventurerCardPreview(profile);

let lastSavedProfile = {
    name: '',
    background: '',
    hobby: '',
    weaponSkill: '',
    spellGoal: '',
};

const saveStatusLabel = computed(() => {
    if (saveStatus.value === 'saving') {
        return '保存中...';
    }
    if (saveStatus.value === 'error') {
        return '保存に失敗しました';
    }
    return '';
});

const rememberSavedProfile = (source: AdventurerProfile): void => {
    lastSavedProfile = {
        name: source.name,
        background: source.background,
        hobby: source.hobby,
        weaponSkill: source.weaponSkill,
        spellGoal: source.spellGoal,
    };
};

const hasProfileChanged = (): boolean => {
    if (!profile.value) {
        return false;
    }

    return (
        profile.value.name !== lastSavedProfile.name ||
        profile.value.background !== lastSavedProfile.background ||
        profile.value.hobby !== lastSavedProfile.hobby ||
        profile.value.weaponSkill !== lastSavedProfile.weaponSkill ||
        profile.value.spellGoal !== lastSavedProfile.spellGoal
    );
};

const applyProfileUpdate = (updated: AdventurerProfile): void => {
    if (!profile.value) {
        profile.value = updated;
        return;
    }

    profile.value = {
        ...profile.value,
        ...updated,
        stats: updated.stats ?? profile.value.stats,
    };
};

const loadProfile = async (): Promise<void> => {
    isLoading.value = true;
    loadError.value = '';

    try {
        profile.value = await fetchStudentProfile();
        rememberSavedProfile(profile.value);
    } catch {
        loadError.value = 'プロフィールの取得に失敗しました。';
        profile.value = null;
    } finally {
        isLoading.value = false;
    }
};

const persistProfile = async (): Promise<void> => {
    if (!isStudent.value || !profile.value || !hasProfileChanged()) {
        return;
    }

    saveStatus.value = 'saving';

    try {
        const updated = await updateStudentProfile({
            name: profile.value.name,
            background: profile.value.background,
            hobby: profile.value.hobby,
            weaponSkill: profile.value.weaponSkill,
            spellGoal: profile.value.spellGoal,
        });

        applyProfileUpdate(updated);
        rememberSavedProfile(profile.value);

        if (user.value) {
            user.value = {
                ...user.value,
                name: updated.name,
            };
        }

        saveStatus.value = 'idle';
    } catch {
        saveStatus.value = 'error';
    }
};

export function useStudentProfile() {
    onMounted(() => {
        void loadProfile();
    });

    return {
        profile,
        isLoading,
        loadError,
        saveStatus,
        saveStatusLabel,
        slackPreview,
        showToast,
        toastMessage,
        isStudent,
        loadProfile,
        persistProfile,
        applyProfileUpdate,
        copyAdventurerCard,
    };
}
