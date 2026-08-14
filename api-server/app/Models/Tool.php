<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    protected $fillable = [
        'name',
        'icon',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public static function normalizeName(string $name): string
    {
        return strtolower(trim($name));
    }

    public function quests(): HasMany
    {
        return $this->hasMany(Quest::class);
    }
}
