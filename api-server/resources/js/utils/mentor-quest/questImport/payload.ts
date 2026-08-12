import type { QuestImportItem, QuestImportPayloadItem } from '@/types/mentor-quest/questImport';
import { serializeQuestDescriptionSections } from '@/utils/quest-sheet/questDescriptionSections';

const toChildQuestPayload = (
    item: QuestImportItem,
): QuestImportPayloadItem => {
    const {
        clientId: _clientId,
        action: _action,
        existingId: _existingId,
        errors: _errors,
        csvNo: _csvNo,
        unitSortOrder: _unitSortOrder,
        todoNote: _todoNote,
        overview,
        purpose,
        deliverable,
        completionCondition,
        description: _description,
        clearCondition: _clearCondition,
        ...rest
    } = item;

    const { description, clearCondition } = serializeQuestDescriptionSections({
        overview: overview ?? '',
        purpose: purpose ?? '',
        deliverable: deliverable ?? '',
        completionCondition: completionCondition ?? '',
    });

    return {
        ...rest,
        description: description || undefined,
        clearCondition: clearCondition || undefined,
    };
};

/** プレビュー専用フィールドを除き、API 送信用に変換 */
export const toImportPayload = (items: QuestImportItem[]): QuestImportPayloadItem[] =>
    items.map((item) => {
        if (item.kind === 'child_quest') {
            return toChildQuestPayload(item);
        }

        const {
            clientId: _clientId,
            action: _action,
            existingId: _existingId,
            errors: _errors,
            csvNo: _csvNo,
            unitSortOrder: _unitSortOrder,
            todoNote: _todoNote,
            overview: _overview,
            purpose: _purpose,
            deliverable: _deliverable,
            completionCondition: _completionCondition,
            ...payload
        } = item;

        return payload;
    });
