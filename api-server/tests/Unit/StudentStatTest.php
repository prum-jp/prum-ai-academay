<?php

namespace Tests\Unit;

use App\Models\StudentStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentStatTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_for_user_reuses_existing_record_when_relation_is_unloaded(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);
        StudentStat::query()->create([
            'user_id' => $student->id,
            'stat_business_skill' => 0,
            'stat_human_skill' => 0,
            'stat_conceptual_skill' => 0,
            'total_xp' => 0,
        ]);

        $first = StudentStat::ensureForUser($student);
        $second = StudentStat::ensureForUser($student);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, StudentStat::query()->where('user_id', $student->id)->count());
    }
}
