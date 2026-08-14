<?php

namespace App\Http\Controllers;

use App\Http\Resources\StudentNotificationResource;
use App\Models\StudentNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = StudentNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $notifications
                ->map(fn (StudentNotification $notification) => (new StudentNotificationResource($notification))->resolve())
                ->values(),
            'meta' => [
                'total' => $notifications->count(),
            ],
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = StudentNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereKey($id)
            ->firstOrFail();

        $notification->read_at = now();
        $notification->save();

        return response()->json([
            'data' => (new StudentNotificationResource($notification))->resolve(),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = StudentNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->whereKey($id)
            ->firstOrFail();

        $notification->delete();

        return response()->json([
            'message' => '通知を削除しました。',
        ]);
    }
}
