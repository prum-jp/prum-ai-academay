<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\AdventurerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['メールアドレスまたはパスワードが正しくありません。'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->toUserPayload($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        AdventurerContext::clearMentorTarget($request);

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'ログアウトしました。',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'user' => $this->toUserPayload($user),
        ]);
    }

    /**
     * @return array{id: int, name: string, email: string, role: int}
     */
    private function toUserPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => (int) $user->role,
        ];
    }
}
