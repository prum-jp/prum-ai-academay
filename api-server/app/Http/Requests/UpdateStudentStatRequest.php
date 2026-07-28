<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentStatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isMentor() === true;
    }

    /**
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>>
     */
    public function rules(): array
    {
        return [
            'stat' => [
                'required',
                'string',
                Rule::in([
                    'presentation',
                    'communication',
                    'problemFinding',
                    'aiAffinity',
                    'action',
                    'support',
                ]),
            ],
            'delta' => ['required', 'integer', Rule::in([-1, 1])],
        ];
    }
}
