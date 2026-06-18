<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isApproved()) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.not_approved'),
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('web_api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => __('auth.not_approved'),
                ], 403);
            }

            return redirect()->route('login')->with('status', __('auth.not_approved'));
        }

        return $next($request);
    }
}
