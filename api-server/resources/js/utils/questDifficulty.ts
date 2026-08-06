export const QUEST_DIFFICULTY_MIN = 1;
export const QUEST_DIFFICULTY_MAX = 5;
export const QUEST_XP_PER_DIFFICULTY_LEVEL = 4;

export const experiencePointsFromDifficulty = (
    difficulty: number | null | undefined,
): number => {
    if (difficulty === null || difficulty === undefined) {
        return 0;
    }

    const level = Math.trunc(difficulty);
    if (level < QUEST_DIFFICULTY_MIN || level > QUEST_DIFFICULTY_MAX) {
        return 0;
    }

    return level * QUEST_XP_PER_DIFFICULTY_LEVEL;
};

export const parseQuestDifficulty = (raw: string): number | undefined => {
    const trimmed = raw.trim();
    if (trimmed === '') {
        return undefined;
    }

    if (/^[1-5]$/.test(trimmed)) {
        const level = Number(trimmed);
        if (level >= QUEST_DIFFICULTY_MIN && level <= QUEST_DIFFICULTY_MAX) {
            return level;
        }
    }

    const starCount = (trimmed.match(/★/g) ?? []).length;
    if (starCount >= QUEST_DIFFICULTY_MIN && starCount <= QUEST_DIFFICULTY_MAX) {
        return starCount;
    }

    return undefined;
};

export const formatQuestDifficultyStars = (difficulty: number | null | undefined): string => {
    if (difficulty === null || difficulty === undefined) {
        return '—';
    }

    const level = Math.min(
        QUEST_DIFFICULTY_MAX,
        Math.max(QUEST_DIFFICULTY_MIN, Math.trunc(difficulty)),
    );

    return '★'.repeat(level) + '☆'.repeat(QUEST_DIFFICULTY_MAX - level);
};

export const parseExperiencePoints = (raw: string): number | undefined => {
    const trimmed = raw.trim().replace(/,/g, '');
    if (trimmed === '') {
        return undefined;
    }

    const value = Number(trimmed);
    if (!Number.isFinite(value) || value < 0) {
        return undefined;
    }

    return Math.trunc(value);
};

export const formatExperiencePoints = (value: number | null | undefined): string => {
    const points = value ?? 0;
    if (points <= 0) {
        return '—';
    }

    return `${points.toLocaleString('ja-JP')} XP`;
};

export const formatExperiencePointsFromDifficulty = (
    difficulty: number | null | undefined,
): string => formatExperiencePoints(experiencePointsFromDifficulty(difficulty));
