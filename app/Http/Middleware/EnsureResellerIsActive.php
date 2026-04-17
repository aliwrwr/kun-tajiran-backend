<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureResellerIsActive
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'غير مصرح'], 401);
        }

        if ($user->role === 'admin') {
            return $next($request);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'حسابك غير مفعل أو محظور'], 403);
        }

        return $next($request);
    }
}
