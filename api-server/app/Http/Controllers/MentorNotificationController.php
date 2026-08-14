<?php

namespace App\Http\Controllers;

use App\Http\Resources\MentorNotificationResource;
use App\Models\MentorNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MentorNotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = MentorNotification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => $notifications
                ->map(fn (MentorNotification $notification) => (new MentorNotificationResource($notification))->resolve())
                ->values(),
            'meta' => [
                'total' => $notifications->count(),
            ],
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notification = MentorNotification::query()
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
