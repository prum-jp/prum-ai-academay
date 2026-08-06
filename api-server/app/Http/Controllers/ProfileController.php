<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentProfileRequest;
use App\Http\Requests\UploadStudentAvatarRequest;
use App\Http\Resources\AdventurerProfileResource;
use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\StudentAvatarService;
use App\Services\StudentExperienceService;
use App\Support\AdventurerContext;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private readonly LevelCalculator $levelCalculator,
        private readonly StudentExperienceService $studentExperienceService,
        private readonly StudentAvatarService $avatarService,
    ) {}

    public function show(Request $request): AdventurerProfileResource
    {
        $student = AdventurerContext::targetStudent($request)
            ->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($student, $this->levelCalculator, $this->studentExperienceService);
    }

    public function update(UpdateStudentProfileRequest $request): AdventurerProfileResource
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'name' => $validated['name'],
        ]);

        $user->studentProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'background' => $validated['background'] ?? '',
                'hobby' => $validated['hobby'] ?? '',
                'weapon_skill' => $validated['weaponSkill'] ?? '',
                'spell_goal' => $validated['spellGoal'] ?? '',
            ],
        );

        $user->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($user, $this->levelCalculator, $this->studentExperienceService);
    }

    public function uploadAvatar(UploadStudentAvatarRequest $request): AdventurerProfileResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->avatarService->store($user, $request->file('avatar'));
        $user->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($user, $this->levelCalculator, $this->studentExperienceService);
    }

    public function deleteAvatar(Request $request): AdventurerProfileResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->avatarService->delete($user);
        $user->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($user, $this->levelCalculator, $this->studentExperienceService);
    }
}
