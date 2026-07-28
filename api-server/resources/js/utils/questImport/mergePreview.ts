import type { QuestImportItem, QuestImportPreviewResponse } from '@/types/questImport';
import { groupImportItemsByUnit } from '@/utils/questImport/groupItems';

const mergePreviewItem = (
    previewItem: QuestImportItem,
    sourceItem: QuestImportItem | undefined,
    index: number,
    preferServerPublish: boolean,
): QuestImportItem => ({
    ...previewItem,
    clientId: sourceItem?.clientId ?? `preview-${index}`,
    csvNo: sourceItem?.csvNo,
    unitSortOrder: sourceItem?.unitSortOrder,
    todoNote: sourceItem?.todoNote,
    difficulty: sourceItem?.difficulty,
    estimatedDuration: sourceItem?.estimatedDuration,
    isPublished: preferServerPublish
        ? previewItem.isPublished
        : (sourceItem?.isPublished ?? previewItem.isPublished),
});

/** サーバープレビュー結果をクライアント状態とマージしてグループ化 */
export const applyPreviewResponse = (
    preview: QuestImportPreviewResponse,
    sourceItems: QuestImportItem[],
    preferServerPublish = false,
): QuestImportItem[] =>
    groupImportItemsByUnit(
        preview.data.map((item, index) =>
            mergePreviewItem(item, sourceItems[index], index, preferServerPublish),
        ),
    );
