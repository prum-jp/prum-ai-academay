<?php

namespace App\Http\Requests;

use App\Models\Quest;
use App\Support\QuestTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMentorPersonalQuestRequest extends FormRequest
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
        /** @var Quest|null $quest */
        $quest = $this->route('quest');
        $questId = $quest?->id;
        $unitId = $quest?->quest_unit_id;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quests', 'title')
                    ->where(
                        fn ($query) => $query
                            ->where('type', Quest::TYPE_PERSONAL)
                            ->where('quest_unit_id', $unitId),
                    )
                    ->ignore($questId),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
            'clearCondition' => ['nullable', 'string', 'max:10000'],
            'toolId' => ['nullable', 'integer', 'exists:tools,id'],
            'estimatedDuration' => ['nullable', 'string', 'max:255'],
            'difficulty' => ['nullable', 'integer', 'min:1', 'max:5'],
            'skillGrants' => ['nullable', 'array'],
            'skillGrants.*' => ['string', Rule::in(\App\Support\SkillKeys::ALL)],
            'questTier' => ['nullable', 'string', Rule::in(QuestTier::ALL)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'クエストタイトルを入力してください。',
            'title.unique' => '同じユニット内に同タイトルのクエストが既に存在します。',
            'toolId.exists' => '指定されたツールが見つかりません。',
            'difficulty.in' => '難度は1〜5で入力してください。',
        ];
    }
}
