<?php

namespace App\Http\Requests;

use App\Rules\UniqueToolName;
use Illuminate\Foundation\Http\FormRequest;

class StoreMentorToolRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
                new UniqueToolName,
            ],
            'icon' => ['nullable', 'string', 'max:80'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '表示名を入力してください。',
            'name.max' => '表示名は255文字以内で入力してください。',
            'icon.max' => 'アイコンは80文字以内で入力してください。',
        ];
    }
}
