<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAcademyMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || (! $user->isStudent() && ! $user->isMentor())) {
            abort(403, '学習者またはメンターのみアクセスできます。');
        }

        return $next($request);
    }
}
