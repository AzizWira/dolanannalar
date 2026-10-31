<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $view->with('site', Setting::site());
        });
        VerifyEmail::toMailUsing(function (User $user, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Verifikasi email Anda — Dolanan Nalar')
                ->view(['html' => 'emails.account', 'text' => 'emails.account-text'], [
                    'name' => $user->name,
                    'url' => $url,
                    'label' => 'SELAMAT DATANG DI RUANG BELAJAR',
                    'heading' => 'Satu langkah untuk terhubung.',
                    'intro' => 'Verifikasi alamat email Anda untuk mulai menggunakan ruang belajar Dolanan Nalar dan mengakses laporan anak yang terhubung dengan akun Anda.',
                    'action' => 'Verifikasi email saya',
                    'note' => 'Jika Anda tidak membuat akun ini, abaikan email ini.',
                    'expires' => config('auth.verification.expire', 60),
                ]);
        });
        ResetPassword::toMailUsing(function (User $user, string $token): MailMessage {
            $url = url(route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));

            return (new MailMessage)
                ->subject('Atur ulang password — Dolanan Nalar')
                ->view(['html' => 'emails.account', 'text' => 'emails.account-text'], [
                    'name' => $user->name,
                    'url' => $url,
                    'label' => 'PEMULIHAN AKUN',
                    'heading' => 'Mari kembali ke ruang belajar.',
                    'intro' => 'Kami menerima permintaan untuk mengatur ulang password akun Anda. Gunakan tombol di bawah untuk membuat password baru.',
                    'action' => 'Buat password baru',
                    'note' => 'Jika Anda tidak meminta perubahan password, abaikan email ini. Password Anda tetap sama.',
                    'expires' => config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60),
                ]);
        });
        Paginator::defaultView('components.pagination');
    }
}
