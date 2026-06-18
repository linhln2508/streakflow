<?php

namespace App\Http\Controllers\WebApi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\User;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    use RespondsWithJsonApi;

    public function __construct(
        protected GamificationService $gamification,
    ) {}

    public function approve(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->jsonFail(__('auth.user_approve_admin_forbidden'), 422);
        }

        if ($user->isApproved()) {
            return $this->jsonFail(__('auth.user_already_approved'), 422);
        }

        $user->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        return $this->jsonSuccess(
            $user->only(['id', 'name', 'email', 'role', 'is_approved', 'approved_at']),
            __('auth.user_approved'),
        );
    }

    public function reject(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->jsonFail(__('auth.user_reject_admin_forbidden'), 422);
        }

        if ($user->isApproved()) {
            return $this->jsonFail(__('auth.user_reject_approved_forbidden'), 422);
        }

        $user->delete();

        return $this->jsonSuccess(null, __('auth.user_rejected'));
    }

    public function adjustHp(Request $request, User $user)
    {
        $validator = validator($request->all(), [
            'amount' => 'required|integer|not_in:0|between:-500,500',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $amount = (int) $validator->validated()['amount'];
        $hpBefore = $user->hp;
        $hpAfter = $this->gamification->applyManualHpAdjustment($hpBefore, $amount);

        $user->update(['hp' => $hpAfter]);

        return $this->jsonSuccess([
            'hp_before' => $hpBefore,
            'hp_after' => $hpAfter,
            'amount' => $amount,
            'user' => $user->only(['id', 'name', 'email', 'hp', 'xp', 'level', 'streak_count']),
        ], __('admin.hp_adjusted'));
    }

    public function resetPassword(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return $this->jsonFail(__('auth.user_password_reset_admin_forbidden'), 422);
        }

        $validator = validator($request->all(), [
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $user->update([
            'password' => Hash::make($validator->validated()['password']),
        ]);

        return $this->jsonSuccess(null, __('admin.password_reset'));
    }
}
