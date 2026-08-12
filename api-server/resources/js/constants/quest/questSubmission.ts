export type QuestSubmissionType = 'link' | 'image' | 'video' | 'audio' | 'text';

export const questSubmissionTypes = [
    'link',
    'image',
    'video',
    'audio',
    'text',
] as const;

export const questSubmissionTypeLabels: Record<QuestSubmissionType, string> = {
    link: 'リンク',
    image: '画像',
    video: '動画',
    audio: '音声',
    text: 'テキスト',
};

export const questSubmissionTypeIcons: Record<QuestSubmissionType, string> = {
    link: 'fa-solid fa-link',
    image: 'fa-solid fa-image',
    video: 'fa-solid fa-video',
    audio: 'fa-solid fa-volume-high',
    text: 'fa-solid fa-align-left',
};

export const questSubmissionTypeOptions = questSubmissionTypes.map((value) => ({
    value,
    label: questSubmissionTypeLabels[value],
    icon: questSubmissionTypeIcons[value],
}));

export const DEFAULT_QUEST_SUBMISSION_TYPE: QuestSubmissionType = 'link';

export const questSubmissionAcceptByType: Record<
    Exclude<QuestSubmissionType, 'link' | 'text'>,
    string
> = {
    image: 'image/jpeg,image/png,image/webp,image/gif',
    video: 'video/mp4,video/webm,video/quicktime',
    audio: 'audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/x-m4a',
};

export const questSubmissionMaxBytes: Record<
    Exclude<QuestSubmissionType, 'link' | 'text'>,
    number
> = {
    image: 10 * 1024 * 1024,
    video: 50 * 1024 * 1024,
    audio: 20 * 1024 * 1024,
};

export const questSubmissionMaxSizeLabels: Record<
    Exclude<QuestSubmissionType, 'link' | 'text'>,
    string
> = {
    image: '10MB',
    video: '50MB',
    audio: '20MB',
};

export const isFileSubmissionType = (
    type: QuestSubmissionType,
): type is Exclude<QuestSubmissionType, 'link' | 'text'> =>
    type === 'image' || type === 'video' || type === 'audio';

export const validateSubmissionFile = (
    type: Exclude<QuestSubmissionType, 'link' | 'text'>,
    file: File,
): string | null => {
    const allowed = questSubmissionAcceptByType[type].split(',');
    if (!allowed.includes(file.type)) {
        return questSubmissionMessages.invalidFileType(type);
    }

    if (file.size > questSubmissionMaxBytes[type]) {
        return questSubmissionMessages.maxFileSize(type);
    }

    return null;
};

export const questSubmissionMessages = {
    typeLabel: '提出物の種類',
    linkLabel: 'リンクURL',
    textLabel: 'テキスト',
    fileLabel: 'ファイル',
    chooseFile: 'ファイルを選択',
    noFileSelected: 'ファイルが選択されていません',
    invalidUrl: '有効なURLを入力してください。',
    emptyText: 'テキストを入力してください。',
    emptyFile: 'ファイルを選択してください。',
    invalidFileType: (type: Exclude<QuestSubmissionType, 'link' | 'text'>): string => {
        const labels: Record<typeof type, string> = {
            image: 'jpeg / png / webp / gif',
            video: 'mp4 / webm / mov',
            audio: 'mp3 / wav / ogg / m4a',
        };

        return `${labels[type]} 形式のファイルを選択してください。`;
    },
    maxFileSize: (type: Exclude<QuestSubmissionType, 'link' | 'text'>): string =>
        `ファイルサイズは ${questSubmissionMaxSizeLabels[type]} 以下にしてください。`,
    submitFailed: '提出物の保存に失敗しました。',
    submitSuccess: '提出物を保存しました。',
    savedLabel: '提出済み',
    openLinkLabel: 'リンクを開く',
    openFileLabel: 'ファイルを開く',
    previewImage: '提出画像',
    previewVideo: '提出動画',
    previewAudio: '提出音声',
    previewText: '提出テキスト',
};
