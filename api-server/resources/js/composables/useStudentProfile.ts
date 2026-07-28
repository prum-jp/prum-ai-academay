import { computed, onMounted, ref, watch } from 'vue';
import type { AdventurerProfile, AdventurerStats } from '@/types/adventurer';
import { fetchStudentProfile, updateStudentProfile, updateStudentStat } from '@/api/profile';
import { useAuth } from '@/composables/useAuth';
import { useGameAudio } from '@/composables/useGameAudio';
import { formatAdventurerCard } from '@/utils/formatAdventurerCard';

const profile = ref<AdventurerProfile | null>(null);
const isLoading = ref(true);
const loadError = ref('');
const saveStatus = ref<'idle' | 'saving' | 'error'>('idle');
const isUpdatingStat = ref(false);
const slackPreview = ref('');
const showToast = ref(false);

const { user, isMentor, isStudent } = useAuth();
const { playSound } = useGameAudio();

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

const refreshSlackPreview = (): void => {
    if (!profile.value) {
        slackPreview.value = '';
        return;
    }

    slackPreview.value = formatAdventurerCard(profile.value);
};

watch(
    () => {
        if (!profile.value) {
            return null;
        }

        return {
            name: profile.value.name,
            background: profile.value.background,
            hobby: profile.value.hobby,
            weaponSkill: profile.value.weaponSkill,
            spellGoal: profile.value.spellGoal,
            levelTitle: profile.value.levelTitle,
            stats: { ...profile.value.stats },
        };
    },
    () => {
        refreshSlackPreview();
    },
    { deep: true, immediate: true },
);

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

const changeStat = async (stat: keyof AdventurerStats, delta: 1 | -1): Promise<void> => {
    if (!isMentor.value || !profile.value || isUpdatingStat.value) {
        return;
    }

    const nextValue = profile.value.stats[stat] + delta;
    if (nextValue < 0 || nextValue > 10) {
        playSound('down');
        return;
    }

    isUpdatingStat.value = true;

    try {
        applyProfileUpdate(await updateStudentStat({ stat, delta }));
        playSound(delta > 0 ? 'click' : 'down');
    } catch {
        playSound('down');
        saveStatus.value = 'error';
    } finally {
        isUpdatingStat.value = false;
    }
};

const copyAdventurerCard = async (): Promise<void> => {
    if (!slackPreview.value) {
        return;
    }

    try {
        await navigator.clipboard.writeText(slackPreview.value);
        playSound('level-up');
        showToast.value = true;
        window.setTimeout(() => {
            showToast.value = false;
        }, 2500);
    } catch (error) {
        console.error('Copy failed:', error);
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
        isMentor,
        isStudent,
        loadProfile,
        persistProfile,
        applyProfileUpdate,
        changeStat,
        copyAdventurerCard,
    };
}
