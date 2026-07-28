<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'background' => ['nullable', 'string', 'max:255'],
            'hobby' => ['nullable', 'string', 'max:255'],
            'weaponSkill' => ['nullable', 'string', 'max:5000'],
            'spellGoal' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
