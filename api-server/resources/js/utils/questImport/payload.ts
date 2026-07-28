import type { QuestImportItem, QuestImportPayloadItem } from '@/types/questImport';

/** プレビュー専用フィールドを除いて API 送信用に変換 */
export const toImportPayload = (items: QuestImportItem[]): QuestImportPayloadItem[] =>
    items.map(
        ({
            clientId: _clientId,
            action: _action,
            existingId: _existingId,
            errors: _errors,
            csvNo: _csvNo,
            unitSortOrder: _unitSortOrder,
            todoNote: _todoNote,
            difficulty: _difficulty,
            ...payload
        }) => payload,
    );
