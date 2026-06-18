<?php

namespace App\Http\Middleware;

use App\Services\UnclosedDaysService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'hp' => $user->hp,
                    'xp' => $user->xp,
                    'level' => $user->level,
                    'streak_count' => $user->streak_count,
                    'shield_count' => $user->shield_count,
                    'debt_count' => $user->debt_count,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'closeResult' => fn () => $request->session()->get('closeResult'),
            ],
            'unclosedDaysCount' => fn () => $user
                ? app(UnclosedDaysService::class)->countForUser($user->id)
                : 0,
        ];
    }
}
