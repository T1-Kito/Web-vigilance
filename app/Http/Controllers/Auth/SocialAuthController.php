<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider): RedirectResponse
    {
        if (!in_array($provider, ['google', 'facebook'], true)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Nhà cung cấp đăng nhập không hợp lệ.',
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider): RedirectResponse
    {
        if (!in_array($provider, ['google', 'facebook'], true)) {
            return redirect()->route('login')->withErrors([
                'email' => 'Nhà cung cấp đăng nhập không hợp lệ.',
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors([
                'email' => 'Không thể đăng nhập bằng '.ucfirst($provider).'. Vui lòng thử lại.',
            ]);
        }

        $email = strtolower((string) $socialUser->getEmail());
        if ($email === '') {
            return redirect()->route('login')->withErrors([
                'email' => 'Tài khoản '.ucfirst($provider).' của bạn không có email, không thể đăng nhập.',
            ]);
        }

        $providerIdColumn = $provider.'_id';
        $providerId = (string) $socialUser->getId();

        $user = User::where($providerIdColumn, $providerId)->first();

        if (! $user) {
            $user = User::where('email', $email)->first();
        }

        if (! $user) {
            $name = trim((string) ($socialUser->getName() ?: $socialUser->getNickname() ?: 'User '.Str::upper(Str::random(4))));

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
                'avatar' => $socialUser->getAvatar(),
                $providerIdColumn => $providerId,
                'email_verified_at' => now(),
            ]);
        } else {
            $updateData = [];
            if (empty($user->{$providerIdColumn})) {
                $updateData[$providerIdColumn] = $providerId;
            }

            if (empty($user->avatar) && $socialUser->getAvatar()) {
                $updateData['avatar'] = $socialUser->getAvatar();
            }

            if (is_null($user->email_verified_at)) {
                $updateData['email_verified_at'] = now();
            }

            if (! empty($updateData)) {
                $user->update($updateData);
            }
        }

        Auth::login($user, true);
        request()->session()->regenerate();

        $redirectTo = ($user->role ?? 'user') === 'admin'
            ? route('admin.dashboard')
            : route('home');

        return redirect()->intended($redirectTo);
    }
}
