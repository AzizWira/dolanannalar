@extends('layouts.public')
@section('title','Akun Dolanan Nalar')
@section('content')
<section class="auth-layout container"><aside class="auth-story"><p class="eyebrow">RUANG UNTUK BERTUMBUH</p><h1>Selalu dekat<br>dengan <em>prosesnya.</em></h1><img src="/illustrations/tumbuh-bersama.webp" alt="Buku dan tunas sebagai ilustrasi belajar bertumbuh" width="768" height="512"><p>Setiap langkah kecil layak diperhatikan.</p></aside><div class="auth-panel">
@include('components.messages')
@if($mode==='verify')
    <p class="eyebrow">SATU LANGKAH LAGI</p><h2>Verifikasi email Anda.</h2><p>Kami mengirim tautan ke <strong>{{ auth()->user()->email }}</strong>. Buka tautan tersebut agar data anak hanya dapat diakses pemilik email.</p><div class="verification-actions"><form method="post" action="/verifikasi-email/kirim">@csrf<button class="button dark">Kirim ulang tautan</button></form>@include('components.logout')</div>
@else
    <p class="eyebrow">AKUN DOLANAN NALAR</p><h2>{{ ['login'=>'Selamat datang kembali.','register'=>'Mari terhubung.','forgot'=>'Lupa password?','reset'=>'Buat password baru.'][$mode] }}</h2><p>{{ $mode==='register'?'Gunakan email yang telah dicatat pengelola untuk terhubung dengan anak.':($mode==='forgot'?'Masukkan email akun untuk menerima tautan pemulihan.':'Masuk untuk mengikuti perjalanan belajar anak.') }}</p>
    @if(in_array($mode,['login','register']))
    @if(config('services.google.client_id') && config('services.google.client_secret'))<a class="google-signin" href="/auth/google"><img src="/elements/google-g.png" width="20" height="20" alt="" aria-hidden="true"><span>Lanjutkan dengan Google</span></a>@else<div class="google-pending">Login Google tersedia setelah konfigurasi diaktifkan.</div>@endif
    <div class="form-divider"><span>atau gunakan email</span></div>@endif
    <form method="post" action="{{ ['login'=>'/masuk','register'=>'/daftar','forgot'=>'/lupa-password','reset'=>'/reset-password'][$mode] }}">@csrf
    @if($mode==='reset')<input type="hidden" name="token" value="{{ $token }}">@endif
    @if($mode==='register')<label>Nama orang tua<input name="name" value="{{ old('name') }}" required autocomplete="name" maxlength="100"></label>@endif
    <label>Email<input type="email" name="email" value="{{ old('email',request('email')) }}" required autocomplete="email" placeholder="nama@email.com"></label>
    @if($mode!=='forgot')<label>Password<div class="password-field"><input id="password" type="password" name="password" required autocomplete="{{ $mode==='login'?'current-password':'new-password' }}" @if($mode!=='login') minlength="10" @endif><button type="button" data-password="password" aria-label="Tampilkan password">Lihat</button></div></label>@endif
    @if(in_array($mode,['register','reset']))<p class="field-help">Minimal 10 karakter, termasuk huruf dan angka.</p><label>Ulangi password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>@endif
    @if($mode==='login')<div class="form-between"><label class="checkbox"><input type="checkbox" name="remember" value="1"> Ingat saya</label><a href="/lupa-password">Lupa password?</a></div>@endif
    <button class="button dark full">{{ ['login'=>'Masuk ke akun','register'=>'Buat akun orang tua','forgot'=>'Kirim tautan pemulihan','reset'=>'Simpan password'][$mode] }} <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">@if($mode==='login')Belum punya akun? <a href="/daftar">Daftar di sini</a>@else<a href="/masuk">Kembali ke halaman masuk</a>@endif</p>
@endif
</div></section>
@endsection
