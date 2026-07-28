<template>
    <RpgStatusCard
        v-if="isLoading"
        title="読み込み中"
        icon="fa-solid fa-spinner"
        message="冒険者データを召喚しています..."
    />

    <RpgStatusCard
        v-else-if="loadError"
        title="取得失敗"
        icon="fa-solid fa-triangle-exclamation"
        variant="error"
        :message="loadError"
        show-retry
        @retry="loadProfile"
    />

    <template v-else-if="profile">
        <div>
            <StudentBasicStatusCard
                :profile="profile"
                :editable="isStudent"
                :save-status="saveStatus"
                :save-status-label="saveStatusLabel"
                @persist="persistProfile"
                @profile-updated="applyProfileUpdate"
            />

            <!-- ビジネス戦闘力配分の +/- はメンターのみ表示 -->
            <RpgCard title="ビジネス戦闘力配分" icon="fa-solid fa-bolt">
                <template #title-extra>
                    <BadgeCountPill
                        :earned-count="profile.earnedBadgeCount"
                        :total-count="profile.totalBadgeCount"
                    />
                </template>
                <LevelPanel
                    :level-title="profile.levelTitle"
                    :progress-percent="profile.progressPercent"
                />
                <StatRow
                    v-for="stat in statDefinitions"
                    :key="stat.key"
                    :label="stat.label"
                    :icon="stat.icon"
                    :value="profile.stats[stat.key]"
                    :editable="isMentor"
                    @increase="changeStat(stat.key, 1)"
                    @decrease="changeStat(stat.key, -1)"
                />
            </RpgCard>
        </div>

        <div>
            <RpgCard title="スキル装備スロット" icon="fa-solid fa-gavel">
                <EquipmentSlot
                    v-model="profile.weaponSkill"
                    label="【武器】今できること / 得意な業務"
                    placeholder="例：Excelの単純集計作業、クライアントとのアポ調整"
                    icon="fa-solid fa-wand-magic-sparkles"
                    @blur="persistProfile"
                />
                <EquipmentSlot
                    v-model="profile.spellGoal"
                    label="【呪文】習得したいAIスキル"
                    placeholder="例：プロンプトで会議アジェンダを作る呪文"
                    icon="fa-solid fa-scroll"
                    @blur="persistProfile"
                />
                <p v-if="saveStatusLabel" class="save-status" :class="`is-${saveStatus}`">
                    {{ saveStatusLabel }}
                </p>
            </RpgCard>

            <RpgCard title="冒険者カードプレビュー" icon="fa-solid fa-clipboard-check" variant="preview">
                <textarea class="preview-textarea" :value="slackPreview" readonly></textarea>
                <div class="action-area">
                    <RpgButton icon="fa-solid fa-copy" @click="copyAdventurerCard">
                        Slack共有用コピー！
                    </RpgButton>
                </div>
            </RpgCard>
        </div>
    </template>

    <ToastNotice message="冒険者カードをコピーしました！" :show="showToast" />
</template>

<script setup lang="ts">
import { statDefinitions } from '@/constants/stats';
import { useStudentProfile } from '@/composables/useStudentProfile';
import RpgCard from '@/components/rpg/RpgCard.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import RpgStatusCard from '@/components/rpg/RpgStatusCard.vue';
import StudentBasicStatusCard from '@/components/rpg/StudentBasicStatusCard.vue';
import StatRow from '@/components/rpg/StatRow.vue';
import LevelPanel from '@/components/rpg/LevelPanel.vue';
import BadgeCountPill from '@/components/rpg/BadgeCountPill.vue';
import EquipmentSlot from '@/components/rpg/EquipmentSlot.vue';
import ToastNotice from '@/components/rpg/ToastNotice.vue';

const {
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
} = useStudentProfile();
</script>
