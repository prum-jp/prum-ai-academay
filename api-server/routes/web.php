<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BadgeController;
use App\Http\Controllers\MentorQuestImportController;
use App\Http\Controllers\MentorQuestController;
use App\Http\Controllers\MentorQuestUnitController;
use App\Http\Controllers\MentorStudentController;
use App\Http\Controllers\MentorToolController;
use App\Http\Controllers\ProfileController;
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
        Route::get('/quest-units', [QuestUnitController::class, 'index']);
        Route::patch('/quests/{id}/progress', [QuestController::class, 'toggleProgress']);
        Route::get('/badges', [BadgeController::class, 'index']);
    });

    Route::middleware(['auth', 'student'])->group(function (): void {
        Route::patch('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
        Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar']);
    });

    Route::middleware(['auth', 'mentor'])->group(function (): void {
        Route::get('/mentor/students', [MentorStudentController::class, 'index']);
        Route::post('/mentor/students', [MentorStudentController::class, 'store']);
        Route::put('/mentor/target-student', [MentorStudentController::class, 'select']);
        Route::get('/mentor/tools', [MentorToolController::class, 'index']);
        Route::post('/mentor/tools', [MentorToolController::class, 'store']);
        Route::get('/mentor/quest-units', [MentorQuestUnitController::class, 'index']);
        Route::post('/mentor/quest-units', [MentorQuestUnitController::class, 'store']);
        Route::get('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'show']);
        Route::put('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'update']);
        Route::patch('/mentor/quest-units/{questUnit}/publish', [MentorQuestUnitController::class, 'publish']);
        Route::delete('/mentor/quest-units/{questUnit}', [MentorQuestUnitController::class, 'destroy']);
        Route::get('/mentor/quests', [MentorQuestController::class, 'index']);
        Route::post('/mentor/quests', [MentorQuestController::class, 'store']);
        Route::put('/mentor/quests/{quest}', [MentorQuestController::class, 'update']);
        Route::patch('/mentor/quests/{quest}/publish', [MentorQuestController::class, 'publish']);
        Route::delete('/mentor/quests/{quest}', [MentorQuestController::class, 'destroy']);
        Route::post('/mentor/quests/import/preview', [MentorQuestImportController::class, 'preview']);
        Route::post('/mentor/quests/import/apply', [MentorQuestImportController::class, 'apply']);
        Route::patch('/profile/stats', [ProfileController::class, 'updateStat']);
    });
});

Route::view('/{any?}', 'app')->where('any', '.*');
