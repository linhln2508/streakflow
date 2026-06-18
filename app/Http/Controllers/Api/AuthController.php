<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use RespondsWithJsonApi;

    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->jsonFail(__('auth.failed'), 401);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return $this->jsonSuccess([
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'role', 'hp', 'xp', 'level', 'streak_count']),
        ], __('auth.login_success'));
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->jsonSuccess(null, __('auth.logout_success'));
    }

    public function me(Request $request)
    {
        return $this->jsonSuccess($request->user()->only([
            'id', 'name', 'email', 'role', 'hp', 'xp', 'level', 'streak_count', 'shield_count',
        ]));
    }
}
