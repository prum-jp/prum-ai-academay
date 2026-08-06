<?php

namespace App\Http\Requests;

use App\Support\QuestTier;
use App\Support\SkillKeys;
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
            'quests' => ['nullable', 'array'],
            'quests.*.title' => ['required', 'string', 'max:255'],
            'quests.*.description' => ['nullable', 'string', 'max:2000'],
            'quests.*.clearCondition' => ['nullable', 'string', 'max:2000'],
            'quests.*.toolId' => ['nullable', 'integer', 'exists:tools,id'],
            'quests.*.sortOrder' => ['nullable', 'integer', 'min:1'],
            'quests.*.difficulty' => ['nullable', 'integer', 'min:1', 'max:5'],
            'quests.*.skillGrants' => ['nullable', 'array'],
            'quests.*.skillGrants.*' => ['string', Rule::in(SkillKeys::ALL)],
            'quests.*.questTier' => ['nullable', 'string', Rule::in(QuestTier::ALL)],
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
        ];
    }
}
