<?php

namespace App\Http\Controllers\WebApi;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebApi\Concerns\RespondsWithJsonApi;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithJsonApi;

    public function login(LoginRequest $request)
    {
        $request->authenticate();
        $request->session()->regenerate();

        return $this->jsonSuccess([
            'redirect' => route('dashboard', absolute: false),
        ], __('auth.login_success'));
    }

    public function register(Request $request)
    {
        $validator = validator($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $validated = $validator->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return $this->jsonSuccess([
            'redirect' => route('dashboard', absolute: false),
        ], __('auth.register_success'));
    }

    public function forgotPassword(Request $request)
    {
        $validator = validator($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $status = Password::sendResetLink($request->only('email'));

        if ($status !== Password::RESET_LINK_SENT) {
            return $this->jsonFail(trans($status), 422, [
                'errors' => ['email' => [trans($status)]],
            ]);
        }

        return $this->jsonSuccess([
            'status' => __($status),
        ], __($status));
    }

    public function resetPassword(Request $request)
    {
        $validator = validator($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->jsonFail(trans($status), 422, [
                'errors' => ['email' => [trans($status)]],
            ]);
        }

        return $this->jsonSuccess([
            'redirect' => route('login', absolute: false),
            'status' => __($status),
        ], __($status));
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->jsonSuccess([
            'redirect' => '/',
        ], __('auth.logout_success'));
    }

    public function confirmPassword(Request $request)
    {
        $validator = validator($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->jsonValidationError($validator);
        }

        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => [__('auth.password')],
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        return $this->jsonSuccess([
            'redirect' => $request->session()->pull('url.intended', route('dashboard', absolute: false)),
        ], __('auth.password_confirmed'));
    }

    public function sendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->jsonSuccess([
                'redirect' => route('dashboard', absolute: false),
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->jsonSuccess([
            'status' => 'verification-link-sent',
        ], __('auth.verification_sent'));
    }
}
