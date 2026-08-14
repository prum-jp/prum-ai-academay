<?php

namespace App\Rules;

use App\Models\Tool;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueToolName implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = Tool::normalizeName((string) $value);
        if ($normalized === '') {
            return;
        }

        $query = Tool::query()->whereRaw('LOWER(TRIM(name)) = ?', [$normalized]);

        if ($this->ignoreId !== null) {
            $query->where('id', '!=', $this->ignoreId);
        }

        if ($query->exists()) {
            $fail('この表示名は既に登録されています。');
        }
    }
}
