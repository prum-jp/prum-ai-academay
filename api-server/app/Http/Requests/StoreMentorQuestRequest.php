<?php

namespace App\Http\Requests;

use App\Models\Quest;
use App\Support\SkillKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMentorQuestRequest extends FormRequest
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
        $type = $this->input('type');

        return [
            'type' => ['required', 'string', Rule::in([Quest::TYPE_TEAM, Quest::TYPE_SPECIAL])],
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quests', 'title')->where(
                    fn ($query) => $query
                        ->where('type', $type)
                        ->whereNull('quest_unit_id'),
                ),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'clearCondition' => ['nullable', 'string', 'max:2000'],
            'isRequired' => ['sometimes', 'boolean'],
            'unlockLevel' => ['nullable', 'integer', 'min:1', 'max:99'],
            'rewardText' => ['nullable', 'string', 'max:500'],
            'badgeLabel' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['nullable', 'integer', 'min:1', 'max:5'],
            'toolId' => ['nullable', 'integer', 'exists:tools,id'],
            'toolIds' => ['nullable', 'array'],
            'toolIds.*' => ['integer', 'exists:tools,id'],
            'skillGrants' => ['nullable', 'array'],
            'skillGrants.*' => ['string', Rule::in(SkillKeys::ALL)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'クエスト種別を選択してください。',
            'type.in' => 'クエスト種別の指定が不正です。',
            'title.required' => 'クエストタイトルを入力してください。',
            'title.unique' => '同じ種別に同タイトルのクエストが既に存在します。',
            'skillGrants.*.in' => '付与スキルの指定が不正です。',
        ];
    }
}
