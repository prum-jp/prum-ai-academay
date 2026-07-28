<?php

namespace App\Http\Resources;

use App\Models\Badge;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Badge
 */
class BadgeResource extends JsonResource
{
    public static $wrap = null;

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $earned = $this->relationLoaded('studentBadges')
            ? $this->studentBadges->isNotEmpty()
            : false;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description ?? '',
            'icon' => $this->icon,
            'isEarned' => $earned,
        ];
    }
}
