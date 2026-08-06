export const getDisplayInitial = (name: string): string => {
    const trimmed = name.trim();
    if (trimmed === '') {
        return '?';
    }

    return trimmed.charAt(0);
};
