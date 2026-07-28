export const AVATAR_MAX_BYTES = 5 * 1024 * 1024;

export const AVATAR_MAX_SIZE_LABEL = '5MB';

export const AVATAR_ALLOWED_MIME_TYPES = [
    'image/jpeg',
    'image/png',
    'image/webp',
] as const;

export const AVATAR_ACCEPT = AVATAR_ALLOWED_MIME_TYPES.join(',');

export const avatarConfig = {
    hint: `※お気に入りの顔写真を設定しよう！（${AVATAR_MAX_SIZE_LABEL}以下）`,
    placeholderLabel: 'HERO',
} as const;

export const avatarMessages = {
    maxSize: `画像サイズは ${AVATAR_MAX_SIZE_LABEL} 以下にしてください。`,
    invalidType: 'jpeg / png / webp 形式の画像を選択してください。',
    uploadFailed: '画像のアップロードに失敗しました。',
    resetFailed: '画像のリセットに失敗しました。',
} as const;

export const validateAvatarFile = (file: File): string | null => {
    if (!AVATAR_ALLOWED_MIME_TYPES.includes(file.type as (typeof AVATAR_ALLOWED_MIME_TYPES)[number])) {
        return avatarMessages.invalidType;
    }

    if (file.size > AVATAR_MAX_BYTES) {
        return avatarMessages.maxSize;
    }

    return null;
};
