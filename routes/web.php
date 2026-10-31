<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\TutorPhotoController;
use App\Http\Middleware\ActiveUser;
use App\Models\User;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('public.home'))->name('home');
Route::get('/layanan', fn () => view('public.services'));
Route::get('/harga', fn () => view('public.pricing'));
Route::get('/foto-tentor/{tutor}', TutorPhotoController::class);
Route::get('/tentor', fn () => view('public.tutors', ['tutors' => User::where('role', 'tutor')->where('active', true)->where('public_profile', true)->get()]));
Route::get('/perkembangan-anak', fn () => auth()->check() ? redirect('/dashboard') : view('public.parents'));
Route::get('/sitemap.xml', fn () => response()->view('public.sitemap')->header('Content-Type', 'application/xml'));
Route::get('/robots.txt', fn () => response("User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /laporan\nDisallow: /kelola\nDisallow: /masuk\nSitemap: ".url('/sitemap.xml'))->header('Content-Type', 'text/plain'));
Route::get('/masuk', fn () => (new AuthController)->form())->name('login');
Route::get('/daftar', fn () => (new AuthController)->form('register'));
Route::post('/masuk', [AuthController::class, 'login'])->middleware('throttle:6,1');
Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:4,1');
Route::post('/keluar', [AuthController::class, 'logout'])->middleware('auth');
Route::get('/lupa-password', fn () => (new AuthController)->form('forgot'))->name('password.request');
Route::post('/lupa-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
Route::get('/reset-password/{token}', fn (string $token) => view('auth.form', ['mode' => 'reset', 'token' => $token]))->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
Route::get('/auth/google', [AuthController::class, 'google'])->middleware('throttle:10,1');
Route::get('/auth/google/callback', [AuthController::class, 'callback'])->middleware('throttle:10,1');
Route::middleware(['auth', ActiveUser::class])->group(function () {
    Route::get('/verifikasi-email', fn () => view('auth.form', ['mode' => 'verify']))->name('verification.notice');
    Route::get('/verifikasi-email/{id}/{hash}', function (EmailVerificationRequest $r) {
        $r->fulfill();

        return redirect('/dashboard');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/verifikasi-email/kirim', function (Request $r) {
        $r->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Tautan verifikasi dikirim ulang.');
    })->middleware('throttle:3,1')->name('verification.send');
});
Route::middleware(['auth', ActiveUser::class, 'verified'])->group(function () {
    Route::get('/dashboard', [LearningController::class, 'dashboard'])->name('dashboard');
    Route::get('/laporan/buat', [LearningController::class, 'form']);
    Route::post('/laporan', [LearningController::class, 'save'])->middleware('throttle:30,1');
    Route::get('/laporan/{report}', [LearningController::class, 'show']);
    Route::get('/laporan/{report}/edit', [LearningController::class, 'form']);
    Route::put('/laporan/{report}', [LearningController::class, 'save'])->middleware('throttle:30,1');
    Route::get('/lampiran/{attachment}', [LearningController::class, 'download']);
    Route::get('/kelola/murid', [AdminController::class, 'students']);
    Route::post('/kelola/murid', [AdminController::class, 'saveStudent']);
    Route::put('/kelola/murid/{student}', [AdminController::class, 'saveStudent']);
    Route::get('/kelola/tentor', [AdminController::class, 'tutors']);
    Route::post('/kelola/tentor', [AdminController::class, 'saveTutor']);
    Route::put('/kelola/tentor/{tutor}', [AdminController::class, 'saveTutor']);
    Route::get('/kelola/konten',[AdminController::class, 'content']);
    Route::put('/kelola/konten',[AdminController::class, 'saveContent']);
});
