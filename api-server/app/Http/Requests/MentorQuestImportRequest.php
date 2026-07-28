<?php

namespace App\Http\Requests;

use App\Support\GrowthStatKeys;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MentorQuestImportRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.kind' => [
                'required',
                'string',
                Rule::in(['personal_unit', 'child_quest', 'team_quest', 'special_quest']),
            ],
            'items.*.id' => ['nullable', 'integer', 'min:1'],
            'items.*.unitTitle' => ['nullable', 'string', 'max:255'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string', 'max:10000'],
            'items.*.rewardText' => ['nullable', 'string', 'max:500'],
            'items.*.clearCondition' => ['nullable', 'string', 'max:5000'],
            'items.*.estimatedDuration' => ['nullable', 'string', 'max:40'],
            'items.*.toolCode' => ['nullable', 'string', 'max:40'],
            'items.*.rewards' => ['nullable', 'array'],
            'items.*.rewards.*.stat' => ['required', 'string', Rule::in(GrowthStatKeys::ALL)],
            'items.*.rewards.*.points' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.isPublished' => ['required', 'boolean'],
            'items.*.sortOrder' => ['nullable', 'integer', 'min:0'],
            'items.*.isRequired' => ['nullable', 'boolean'],
            'items.*.unlockLevel' => ['nullable', 'integer', 'min:1', 'max:99'],
            'items.*.badgeLabel' => ['nullable', 'string', 'max:255'],
        ];
    }
}
