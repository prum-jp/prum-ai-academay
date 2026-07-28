<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'code' => [
                'required',
                'string',
                'max:40',
                'alpha_dash',
                Rule::unique('tools', 'code'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:80'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => '識別コードを入力してください。',
            'code.max' => '識別コードは40文字以内で入力してください。',
            'code.alpha_dash' => '識別コードは英数字とハイフン・アンダースコアのみ使用できます。',
            'code.unique' => 'この識別コードは既に登録されています。',
            'name.required' => '表示名を入力してください。',
            'name.max' => '表示名は255文字以内で入力してください。',
            'icon.max' => 'アイコンは80文字以内で入力してください。',
        ];
    }
}
