<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateStudentProfileRequest;
use App\Http\Requests\UpdateStudentStatRequest;
use App\Http\Requests\UploadStudentAvatarRequest;
use App\Http\Resources\AdventurerProfileResource;
use App\Models\User;
use App\Services\LevelCalculator;
use App\Services\StudentAvatarService;
use App\Support\AdventurerContext;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    private const STAT_COLUMN_MAP = [
        'presentation' => 'stat_presentation',
        'communication' => 'stat_communication',
        'problemFinding' => 'stat_problem_finding',
        'aiAffinity' => 'stat_ai_affinity',
        'action' => 'stat_action',
        'support' => 'stat_support',
    ];

    public function __construct(
        private readonly LevelCalculator $levelCalculator,
        private readonly StudentAvatarService $avatarService,
    ) {}

    public function show(Request $request): AdventurerProfileResource
    {
        $student = AdventurerContext::targetStudent($request)
            ->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($student, $this->levelCalculator);
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

        return new AdventurerProfileResource($user, $this->levelCalculator);
    }

    public function uploadAvatar(UploadStudentAvatarRequest $request): AdventurerProfileResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->avatarService->store($user, $request->file('avatar'));
        $user->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($user, $this->levelCalculator);
    }

    public function deleteAvatar(Request $request): AdventurerProfileResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->avatarService->delete($user);
        $user->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($user, $this->levelCalculator);
    }

    public function updateStat(UpdateStudentStatRequest $request): AdventurerProfileResource
    {
        $student = AdventurerContext::targetStudent($request);
        $validated = $request->validated();

        $column = self::STAT_COLUMN_MAP[$validated['stat']];
        $stat = $student->studentStat()->firstOrCreate(
            ['user_id' => $student->id],
            [
                'stat_presentation' => 0,
                'stat_communication' => 0,
                'stat_problem_finding' => 0,
                'stat_ai_affinity' => 0,
                'stat_action' => 0,
                'stat_support' => 0,
            ],
        );

        $nextValue = (int) $stat->{$column} + (int) $validated['delta'];

        if ($nextValue < 0 || $nextValue > 10) {
            throw ValidationException::withMessages([
                'stat' => ['ステータスは 0〜10 の範囲で設定してください。'],
            ]);
        }

        $stat->update([
            $column => $nextValue,
        ]);

        $student->load(['studentProfile', 'studentStat']);

        return new AdventurerProfileResource($student, $this->levelCalculator);
    }
}
