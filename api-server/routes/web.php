<?php

use App\Http\Controllers\AuthController;
// TODO: 後に機能追加 — 実績バッジ API
// use App\Http\Controllers\BadgeController;
use App\Http\Controllers\MentorCurriculumController;
use App\Http\Controllers\MentorQuestImportController;
use App\Http\Controllers\MentorQuestMasterController;
use App\Http\Controllers\MentorQuestController;
use App\Http\Controllers\MentorQuestUnitAssignmentController;
use App\Http\Controllers\MentorQuestUnitController;
use App\Http\Controllers\MentorStudentQuestUnitAssignmentController;
use App\Http\Controllers\MentorStudentAssignmentController;
use App\Http\Controllers\MentorQuestProgressController;
use App\Http\Controllers\MentorNotificationController;
use App\Http\Controllers\MentorReviewRequestController;
use App\Http\Controllers\MentorStudentController;
use App\Http\Controllers\MentorToolController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StudentDirectoryController;
use App\Http\Controllers\StudentNotificationController;
use App\Http\Controllers\QuestCommentController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\QuestUnitController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function (): void {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth');

    Route::middleware(['auth', 'academy'])->group(function (): void {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::get('/quests', [QuestController::class, 'index']);
        Route::get('/quests/{id}', [QuestController::class, 'show']);
        Route::get('/quest-units', [QuestUnitController::class, 'index']);
        Route::get('/quest-units/{questUnitId}', [QuestUnitController::class, 'show']);
        Route::patch('/quests/{id}/progress', [QuestController::class, 'updateProgress']);
        Route::post('/quests/{id}/submission', [QuestController::class, 'updateSubmission']);
        Route::patch('/quests/{id}/submission', [QuestController::class, 'updateSubmission']);
        Route::get('/quests/{id}/comments', [QuestCommentController::class, 'index']);
        Route::post('/quests/{id}/comments', [QuestCommentController::class, 'store']);
        // TODO: 後に機能追加 — 実績バッジ一覧 API
        // Route::get('/badges', [BadgeController::class, 'index']);
        Route::get('/students', [StudentDirectoryController::class, 'index']);
        Route::get('/students/{studentId}', [StudentDirectoryController::class, 'show']);
    });

    Route::middleware(['auth', 'student'])->group(function (): void {
        Route::get('/notifications', [StudentNotificationController::class, 'index']);
        Route::patch('/notifications/{id}/read', [StudentNotificationController::class, 'markAsRead']);
        Route::delete('/notifications/{id}', [StudentNotificationController::class, 'destroy']);
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    });

    Route::middleware(['auth', 'mentor'])->group(function (): void {
        Route::get('/mentor/students', [MentorStudentController::class, 'index']);
        Route::get('/mentor/notifications', [MentorNotificationController::class, 'index']);
        Route::delete('/mentor/notifications/{id}', [MentorNotificationController::class, 'destroy']);
        Route::get('/mentor/review-requests', [MentorReviewRequestController::class, 'index']);
        Route::patch('/mentor/quests/{questId}/progress', [MentorQuestProgressController::class, 'update']);
        Route::get('/mentor/students/picker', [MentorStudentController::class, 'picker']);
        Route::post('/mentor/students', [MentorStudentController::class, 'store']);
        Route::put('/mentor/target-student', [MentorStudentController::class, 'select']);
        Route::get('/mentor/students/{studentId}/quest-units', [MentorStudentQuestUnitAssignmentController::class, 'index']);
        Route::post('/mentor/students/{studentId}/quest-units/{questUnit}/assign', [MentorStudentQuestUnitAssignmentController::class, 'store']);
        Route::delete('/mentor/students/{studentId}/quest-units/{questUnit}/assign', [MentorStudentQuestUnitAssignmentController::class, 'destroy']);
        Route::post('/mentor/students/{studentId}/quests/{quest}/assign', [MentorStudentQuestUnitAssignmentController::class, 'storeQuest']);
        Route::delete('/mentor/students/{studentId}/quests/{quest}/assign', [MentorStudentQuestUnitAssignmentController::class, 'destroyQuest']);
        Route::get('/mentor/students/{studentId}/assignments', [MentorStudentAssignmentController::class, 'show']);
        Route::put('/mentor/students/{studentId}/assignments', [MentorStudentAssignmentController::class, 'update']);
        Route::get('/mentor/curricula', [MentorCurriculumController::class, 'index']);
        Route::post('/mentor/curricula', [MentorCurriculumController::class, 'store']);
        Route::get('/mentor/curricula/{curriculum}', [MentorCurriculumController::class, 'show']);
        Route::put('/mentor/curricula/{curriculum}', [MentorCurriculumController::class, 'update']);
        Route::delete('/mentor/curricula/{curriculum}', [MentorCurriculumController::class, 'destroy']);
        Route::post('/mentor/curricula/{curriculum}/assign-all-students', [MentorCurriculumController::class, 'assignAllStudents']);
        Route::get('/mentor/tools', [MentorToolController::class, 'index']);
        Route::post('/mentor/tools', [MentorToolController::class, 'store']);
        Route::put('/mentor/tools/{tool}', [MentorToolController::class, 'update']);
        Route::get('/mentor/quest-units', [MentorQuestUnitController::class, 'index']);
        Route::put('/mentor/quest-units/reorder', [MentorQuestUnitController::class, 'reorder']);
        Route::post('/mentor/quest-units', [MentorQuestUnitController::class, 'store']);
        Route::get('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'show']);
        Route::put('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'update']);
        Route::post('/mentor/quest-units/{questUnit}/assign-all-students', [MentorQuestUnitAssignmentController::class, 'assignAllStudents']);
        Route::get('/mentor/quest-units/{questUnit}/deletion-impact', [MentorQuestUnitController::class, 'deletionImpact']);
        Route::delete('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'destroy']);
        Route::get('/mentor/quests/master', [MentorQuestMasterController::class, 'index']);
        Route::get('/mentor/quests/master/export', [MentorQuestMasterController::class, 'export']);
        Route::get('/mentor/quests', [MentorQuestController::class, 'index']);
        Route::post('/mentor/quests', [MentorQuestController::class, 'store']);
        Route::get('/mentor/quests/{quest}/deletion-impact', [MentorQuestController::class, 'deletionImpact']);
        Route::get('/mentor/quests/{quest}', [MentorQuestController::class, 'show']);
        Route::put('/mentor/quests/{quest}', [MentorQuestController::class, 'update']);
        Route::put('/mentor/quests/{quest}/personal', [MentorQuestController::class, 'updatePersonal']);
        Route::patch('/mentor/quests/{quest}/publish', [MentorQuestController::class, 'publish']);
        Route::delete('/mentor/quests/{quest}', [MentorQuestController::class, 'destroy']);
        Route::post('/mentor/quests/import/preview', [MentorQuestImportController::class, 'preview']);
        Route::post('/mentor/quests/import/apply', [MentorQuestImportController::class, 'apply']);
    });
});

Route::view('/{any?}', 'app')->where('any', '.*');
