<?php

namespace App\Http\Requests;

use App\Support\QuestSubmissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateQuestSubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(QuestSubmissionType::ALL)],
            'url' => ['nullable', 'string', 'url', 'max:2048'],
            'text' => ['nullable', 'string', 'max:10000'],
            'file' => ['nullable', 'file'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = (string) $this->input('type', '');

            if ($type === QuestSubmissionType::LINK) {
                if (trim((string) $this->input('url', '')) === '') {
                    $validator->errors()->add('url', 'リンクURLを入力してください。');
                }

                return;
            }

            if ($type === QuestSubmissionType::TEXT) {
                if (trim((string) $this->input('text', '')) === '') {
                    $validator->errors()->add('text', 'テキストを入力してください。');
                }

                return;
            }

            if (! QuestSubmissionType::isFileType($type)) {
                return;
            }

            $file = $this->file('file');
            if ($file === null) {
                $validator->errors()->add('file', 'ファイルを選択してください。');

                return;
            }

            $allowedMimeTypes = QuestSubmissionType::MIME_TYPES[$type] ?? [];
            if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
                $validator->errors()->add('file', 'この形式のファイルはアップロードできません。');
            }

            $maxBytes = QuestSubmissionType::MAX_BYTES[$type] ?? null;
            if ($maxBytes !== null && $file->getSize() > $maxBytes) {
                $validator->errors()->add('file', 'ファイルサイズが上限を超えています。');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => '提出物の種類を選択してください。',
            'type.in' => '提出物の種類が不正です。',
            'url.url' => '有効なURLを入力してください。',
        ];
    }
}
