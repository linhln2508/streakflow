<?php

namespace App\Http\Controllers;

use App\Models\DailySummary;
use App\Models\TaskInstance;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('approval')) {
            if ($request->approval === 'pending') {
                $query->where('is_approved', false);
            } elseif ($request->approval === 'approved') {
                $query->where('is_approved', true);
            }
        }

        $users = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'approval']),
            'pendingCount' => User::where('is_approved', false)->where('role', '!=', 'admin')->count(),
        ]);
    }

    public function userDetail(User $user)
    {
        $summaries = DailySummary::where('user_id', $user->id)
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        return Inertia::render('Admin/UserDetail', [
            'profile' => $user,
            'summaries' => $summaries,
        ]);
    }

    public function analytics()
    {
        $stats = [
            'total_users' => User::count(),
            'pending_users' => User::where('is_approved', false)->where('role', '!=', 'admin')->count(),
            'total_instances' => TaskInstance::count(),
            'total_summaries' => DailySummary::count(),
            'active_users_7d' => DailySummary::where('date', '>=', now()->subDays(7))
                ->distinct('user_id')
                ->count('user_id'),
        ];

        $dailyActivity = DailySummary::selectRaw('date, count(*) as count')
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Admin/Analytics', [
            'stats' => $stats,
            'dailyActivity' => $dailyActivity,
        ]);
    }
}
