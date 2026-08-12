import type { QuestImportItem, QuestImportPreviewResponse } from '@/types/mentor-quest/questImport';
import { parseQuestDescriptionSections } from '@/utils/quest-sheet/questDescriptionSections';
import { groupImportItemsByUnit } from '@/utils/mentor-quest/questImport/groupItems';

const mergePreviewItem = (
    previewItem: QuestImportItem,
    sourceItem: QuestImportItem | undefined,
    index: number,
): QuestImportItem => {
    const parsedSections =
        previewItem.kind === 'child_quest'
            ? parseQuestDescriptionSections(
                  previewItem.description ?? '',
                  previewItem.clearCondition ?? '',
              )
            : null;

    return {
        ...previewItem,
        clientId: sourceItem?.clientId ?? `preview-${index}`,
        csvNo: sourceItem?.csvNo,
        unitSortOrder: sourceItem?.unitSortOrder,
        todoNote: sourceItem?.todoNote,
        difficulty: sourceItem?.difficulty ?? previewItem.difficulty,
        questTier: sourceItem?.questTier ?? previewItem.questTier,
        isRequired: sourceItem?.isRequired ?? previewItem.isRequired,
        skillGrants: sourceItem?.skillGrants ?? previewItem.skillGrants ?? [],
        overview: sourceItem?.overview ?? parsedSections?.overview ?? undefined,
        purpose: sourceItem?.purpose ?? parsedSections?.purpose ?? undefined,
        deliverable: sourceItem?.deliverable ?? parsedSections?.deliverable ?? undefined,
        completionCondition:
            sourceItem?.completionCondition ?? parsedSections?.completionCondition ?? undefined,
    };
};

/** サーバープレビュー結果をクライアント状態とマージしてグループ化 */
export const applyPreviewResponse = (
    preview: QuestImportPreviewResponse,
    sourceItems: QuestImportItem[],
): QuestImportItem[] =>
    groupImportItemsByUnit(
        preview.data.map((item, index) => mergePreviewItem(item, sourceItems[index], index)),
    );
