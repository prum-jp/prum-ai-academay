<?php

namespace App\Http\Requests;

use App\Support\SkillKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMentorQuestRequest extends FormRequest
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
        $quest = $this->route('quest');
        $questId = $quest?->id;
        $type = $quest?->type;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quests', 'title')
                    ->where(fn ($query) => $query->where('type', $type)->whereNull('quest_unit_id'))
                    ->ignore($questId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'clearCondition' => ['nullable', 'string', 'max:2000'],
            'isRequired' => ['sometimes', 'boolean'],
            'unlockLevel' => ['nullable', 'integer', 'min:1', 'max:99'],
            'rewardText' => ['nullable', 'string', 'max:500'],
            'badgeLabel' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['nullable', 'integer', 'min:1', 'max:5'],
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
            'title.required' => 'クエストタイトルを入力してください。',
            'title.unique' => '同じ種別に同タイトルのクエストが既に存在します。',
            'skillGrants.*.in' => '付与スキルの指定が不正です。',
        ];
    }
}
