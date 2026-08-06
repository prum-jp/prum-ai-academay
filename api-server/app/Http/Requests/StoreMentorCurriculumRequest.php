<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMentorCurriculumRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:curricula,name'],
            'description' => ['nullable', 'string'],
            'unitIds' => ['sometimes', 'array'],
            'unitIds.*' => ['integer', Rule::exists('quest_units', 'id')],
            'assignmentTarget' => ['required', 'string', Rule::in(['all', 'selected'])],
            'studentIds' => ['required_if:assignmentTarget,selected', 'array', 'min:1'],
            'studentIds.*' => [
                'integer',
                Rule::exists('users', 'id')->where(
                    fn ($query) => $query->where('role', User::ROLE_STUDENT),
                ),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'studentIds.required_if' => '個別選択の場合は、反映する受講生を1人以上選んでください。',
        ];
    }
}
