<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesQuestSubmissionFile;
use App\Support\QuestSubmissionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddQuestSubmissionImageRequest extends FormRequest
{
    use ValidatesQuestSubmissionFile;

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
            'file' => ['required', 'file'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validateSubmissionFile(
                $validator,
                $this->file('file'),
                QuestSubmissionType::IMAGE,
            );
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'ファイルを選択してください。',
        ];
    }
}
