<template>
    <div>
        <StudentBasicStatusCard
            :profile="profile"
            :editable="editable"
            :save-status="saveStatus"
            :save-status-label="saveStatusLabel"
            @persist="$emit('persist')"
            @profile-updated="$emit('profile-updated', $event)"
        />

        <RpgCard title="スキル" icon="fa-solid fa-bolt">
            <!-- TODO: 後に機能追加 — 獲得バッジ数表示 -->
            <!--
            <template #title-extra>
                <BadgeCountPill
                    :earned-count="profile.earnedBadgeCount"
                    :total-count="profile.totalBadgeCount"
                />
            </template>
            -->
            <LevelPanel
                :level-title="profile.levelTitle"
                :progress-percent="profile.progressPercent"
                :total-xp="profile.total"
                :xp-next-level-min="profile.xpNextLevelMin"
            />
            <StatRow
                v-for="stat in skillDefinitions"
                :key="stat.key"
                :label="stat.label"
                :icon="stat.icon"
                :value="profile.stats[stat.key]"
            />
        </RpgCard>
    </div>

    <div>
        <RpgCard title="スキル情報" icon="fa-solid fa-gavel">
            <EquipmentSlot
                v-model="profile.weaponSkill"
                label="今できること / 得意な業務"
                :placeholder="weaponPlaceholder"
                icon="fa-solid fa-wand-magic-sparkles"
                :readonly="!editable"
                @blur="$emit('persist')"
            />
            <EquipmentSlot
                v-model="profile.spellGoal"
                label="習得したいAIスキル"
                :placeholder="spellPlaceholder"
                icon="fa-solid fa-scroll"
                :readonly="!editable"
                @blur="$emit('persist')"
            />
            <p v-if="saveStatusLabel" class="save-status" :class="`is-${saveStatus}`">
                {{ saveStatusLabel }}
            </p>
        </RpgCard>

        <RpgCard title="受講者カードプレビュー" icon="fa-solid fa-clipboard-check" variant="preview">
            <textarea class="preview-textarea" :value="slackPreview" readonly></textarea>
            <div class="action-area">
                <RpgButton icon="fa-solid fa-copy" @click="$emit('copy-card')">
                    Slack用にコピー
                </RpgButton>
            </div>
        </RpgCard>
    </div>
</template>

<script setup lang="ts">
import type { AdventurerProfile } from '@/types/adventurer';
import { skillDefinitions } from '@/constants/skills';
import RpgCard from '@/components/rpg/RpgCard.vue';
import RpgButton from '@/components/rpg/RpgButton.vue';
import StudentBasicStatusCard from '@/components/rpg/StudentBasicStatusCard.vue';
import StatRow from '@/components/rpg/StatRow.vue';
import LevelPanel from '@/components/rpg/LevelPanel.vue';
// TODO: 後に機能追加 — 獲得バッジ数表示
// import BadgeCountPill from '@/components/rpg/BadgeCountPill.vue';
import EquipmentSlot from '@/components/rpg/EquipmentSlot.vue';

withDefaults(
    defineProps<{
        profile: AdventurerProfile;
        editable?: boolean;
        saveStatus?: 'idle' | 'saving' | 'error';
        saveStatusLabel?: string;
        slackPreview: string;
        weaponPlaceholder?: string;
        spellPlaceholder?: string;
    }>(),
    {
        editable: false,
        saveStatus: 'idle',
        saveStatusLabel: '',
        weaponPlaceholder: '例：Excelの単純集計作業、クライアントとのアポ調整',
        spellPlaceholder: '例：プロンプトで会議アジェンダを作成',
    },
);

defineEmits<{
    persist: [];
    'profile-updated': [profile: AdventurerProfile];
    'copy-card': [];
}>();
</script>
