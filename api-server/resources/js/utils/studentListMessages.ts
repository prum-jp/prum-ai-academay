export function resolveSearchEmptyMessage(
    appliedQuery: string,
    emptySearchMessage: string,
    emptyListMessage: string,
): string {
    if (appliedQuery.trim() !== '') {
        return emptySearchMessage;
    }

    return emptyListMessage;
}
