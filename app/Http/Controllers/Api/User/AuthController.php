<?php

namespace App\Http\Controllers\Api\User;

use App\Enums\RolesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Http\Requests\User\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = new \App\Models\User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();

        $role = match ($request->getHost()) {
            env('DOMAIN_APP1') => RolesEnum::DOMAIN1,
            env('DOMAIN_APP2') => RolesEnum::DOMAIN2,
            env('DOMAIN_APP3') => RolesEnum::DOMAIN3,
        };

        $user->assignRole(RolesEnum::USER, $role);

        $token = $user->createToken('auth_token')->plainTextToken;

        event(new Registered($user));

        return response()->json($user->getUserData($token), 201);
    }

    public function login(LoginRequest $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'errors' => [
                    'email' => [__('auth.failed')],
                ],
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json($user->getUserData($token), 201);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => __('auth.success')]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::INVALID_USER) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        return response()->json(['message' => 'Ссылка на восстановление пароля отправлена на вашу почту'], 201);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(\Str::random(60));

                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));
            }
        );

        if ($status === Password::INVALID_USER) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        } elseif ($status === Password::INVALID_TOKEN) {
            return response()->json(['message' => 'Неправильный токен'], 400);
        }

        return response()->json(['message' => 'Пароль успешно изменен']);
    }
}
