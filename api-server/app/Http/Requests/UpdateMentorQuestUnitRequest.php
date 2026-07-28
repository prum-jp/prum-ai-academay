<?php

namespace App\Http\Requests;

use App\Support\GrowthStatKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMentorQuestUnitRequest extends FormRequest
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
        $unitId = $this->route('questUnit')?->id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quest_units', 'title')->ignore($unitId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'rewardText' => ['nullable', 'string', 'max:500'],
            'rewards' => ['nullable', 'array'],
            'rewards.*.stat' => ['required', 'string', Rule::in(GrowthStatKeys::ALL)],
            'rewards.*.points' => ['required', 'integer', 'min:1', 'max:99'],
            'quests' => ['nullable', 'array'],
            'quests.*.id' => ['nullable', 'integer'],
            'quests.*.title' => ['required', 'string', 'max:255'],
            'quests.*.description' => ['nullable', 'string', 'max:2000'],
            'quests.*.clearCondition' => ['nullable', 'string', 'max:2000'],
            'quests.*.toolId' => ['nullable', 'integer', 'exists:tools,id'],
            'quests.*.sortOrder' => ['nullable', 'integer', 'min:1'],
            'quests.*.isPublished' => ['sometimes', 'boolean'],
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
            'quests.*.title.required' => 'クエストタイトルを入力してください。',
            'rewards.*.stat.in' => '成長ステータスの指定が不正です。',
            'rewards.*.points.min' => 'ポイントは1以上で入力してください。',
        ];
    }
}
