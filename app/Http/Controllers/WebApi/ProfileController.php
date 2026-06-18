<?php

namespace App\Http\Controllers\WebApi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use RespondsWithJsonApi;

    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $this->jsonSuccess(
            $user->only(['id', 'name', 'email', 'email_verified_at']),
            __('profile.updated'),
        );
    }

    public function updatePassword(Request $request)
    {
        $validator = validator($request->all(), [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $request->user()->update([
            'password' => Hash::make($validator->validated()['password']),
        ]);

        return $this->jsonSuccess(null, __('profile.password_updated'));
    }

    public function destroy(Request $request)
    {
        $validator = validator($request->all(), [
            'password' => ['required', 'current_password'],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $user = $request->user();

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->jsonSuccess([
            'redirect' => '/',
        ], __('profile.deleted'));
    }
}
