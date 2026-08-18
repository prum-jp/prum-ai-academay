<?php

namespace App\Http\Requests\Concerns;

use App\Support\QuestSubmissionType;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Validator;

trait ValidatesQuestSubmissionFile
{
    protected function validateSubmissionFile(
        Validator $validator,
        ?UploadedFile $file,
        string $type,
        string $field = 'file',
    ): void {
        if ($file === null) {
            $validator->errors()->add($field, 'ファイルを選択してください。');

            return;
        }

        if (! QuestSubmissionType::isFileType($type)) {
            return;
        }

        $allowedMimeTypes = QuestSubmissionType::MIME_TYPES[$type] ?? [];
        if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
            $validator->errors()->add($field, 'この形式のファイルはアップロードできません。');
        }

        $maxBytes = QuestSubmissionType::MAX_BYTES[$type] ?? null;
        if ($maxBytes !== null && $file->getSize() > $maxBytes) {
            $validator->errors()->add($field, 'ファイルサイズが上限を超えています。');
        }
    }
}
