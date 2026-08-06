<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMentorQuestUnitsRequest extends FormRequest
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
            'unitIds' => ['required', 'array', 'min:1'],
            'unitIds.*' => ['required', 'integer', 'exists:quest_units,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'unitIds.required' => '並び替えるユニットを指定してください。',
            'unitIds.*.exists' => '存在しないユニットが含まれています。',
        ];
    }
}
