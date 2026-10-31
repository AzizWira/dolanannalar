<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function form(string $mode = 'login')
    {
        return view('auth.form', compact('mode'));
    }

    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt(['email' => strtolower($data['email']), 'password' => $data['password'], 'active' => true], $r->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => 'Email atau password belum sesuai.']);
        }
        $r->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function register(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
        $data = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:190|unique:users', 'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()]]);
        $u = User::create($data);
        event(new Registered($u));
        Auth::login($u);
        $r->session()->regenerate();

        return redirect('/verifikasi-email');
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect('/');
    }

    public function forgot(Request $r)
    {
        $r->validate(['email' => 'required|email']);
        Password::sendResetLink(['email' => strtolower($r->email)]);

        return back()->with('success', 'Jika email terdaftar, tautan pemulihan akan dikirim.');
    }

    public function reset(Request $r)
    {
        $r->validate(['token' => 'required', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::min(10)->letters()->numbers()]]);
        $status = Password::reset($r->only('email', 'password', 'password_confirmation', 'token'), function (User $u, string $p) {
            $u->forceFill(['password' => Hash::make($p), 'remember_token' => Str::random(60)])->save();
            event(new PasswordReset($u));
        });

        return $status === Password::PASSWORD_RESET ? redirect('/masuk')->with('success', 'Password diperbarui. Silakan masuk.') : back()->withErrors(['email' => 'Tautan tidak valid atau kedaluwarsa.']);
    }

    public function google()
    {
        abort_unless(config('services.google.client_id') && config('services.google.client_secret'), 503, 'Login Google belum dikonfigurasi. Gunakan email dan password.');

        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $r)
    {
        try {
            $g = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect('/masuk')->withErrors(['email' => 'Login Google dibatalkan atau kedaluwarsa. Silakan coba lagi.']);
        }
        abort_unless(($g->user['email_verified'] ?? false) === true, 403);
        $email = strtolower($g->getEmail());
        $u = User::where('google_id', $g->getId())->first() ?? User::where('email', $email)->first();
        if ($u) {
            abort_unless($u->active && $u->role === 'parent' && $u->hasVerifiedEmail(), 403, 'Akun ini perlu masuk dan memverifikasi email terlebih dahulu.');
            abort_if($u->google_id && $u->google_id !== $g->getId(), 403);
        } else {
            $u = User::create(['name' => $g->getName() ?? 'Orang tua', 'email' => $email, 'password' => Str::random(48)]);
        }
        $u->forceFill(['google_id' => $g->getId(), 'email_verified_at' => $u->email_verified_at ?? now()])->save();
        Auth::login($u);
        $r->session()->regenerate();

        return redirect('/dashboard');
    }
}
