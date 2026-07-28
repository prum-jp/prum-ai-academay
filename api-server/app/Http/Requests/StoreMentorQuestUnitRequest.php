<?php

namespace App\Http\Requests;

use App\Support\GrowthStatKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMentorQuestUnitRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255', 'unique:quest_units,title'],
            'description' => ['nullable', 'string', 'max:2000'],
            'rewardText' => ['nullable', 'string', 'max:500'],
            'rewards' => ['nullable', 'array'],
            'rewards.*.stat' => ['required', 'string', Rule::in(GrowthStatKeys::ALL)],
            'rewards.*.points' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'ユニットタイトルを入力してください。',
            'title.unique' => '同じタイトルのユニットが既に存在します。',
            'rewards.*.stat.required' => '成長ステータスを選択してください。',
            'rewards.*.stat.in' => '成長ステータスの指定が不正です。',
            'rewards.*.points.required' => 'ポイントを入力してください。',
            'rewards.*.points.min' => 'ポイントは1以上で入力してください。',
        ];
    }
}
