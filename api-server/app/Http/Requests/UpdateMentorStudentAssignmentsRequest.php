<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMentorStudentAssignmentsRequest extends FormRequest
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
            'curriculumIds' => ['present', 'array'],
            'curriculumIds.*' => ['integer', Rule::exists('curricula', 'id')],
            'unitIds' => ['present', 'array'],
            'unitIds.*' => ['integer', Rule::exists('quest_units', 'id')],
        ];
    }
}
