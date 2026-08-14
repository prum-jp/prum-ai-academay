<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\MentorStudentDeletionService;
use App\Services\StudentAvatarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class MentorStudentDeletionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_delete_removes_student_account(): void
    {
        $student = User::factory()->create(['role' => User::ROLE_STUDENT]);

        $service = new MentorStudentDeletionService(new StudentAvatarService);
        $service->delete($student);

        $this->assertDatabaseMissing('users', ['id' => $student->id]);
    }

    public function test_delete_rejects_non_student(): void
    {
        $mentor = User::factory()->create(['role' => User::ROLE_MENTOR]);

        $service = new MentorStudentDeletionService(new StudentAvatarService);

        $this->expectException(ValidationException::class);
        $service->delete($mentor);
    }
}
