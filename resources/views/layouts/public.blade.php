<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Les Privat di Surakarta') — {{ $site['name'] }}</title>
    <meta name="description" content="{{ $site['description'] }}">
    <meta name="theme-color" content="#174D3C"><link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="{{ $site['name'] }} — {{ $site['slogan'] }}"><meta property="og:image" content="{{ asset('illustrations/belajar-di-rumah.webp') }}">
    @if(request()->is('masuk','daftar','lupa-password','reset-password/*','verifikasi-email*'))<meta name="robots" content="noindex,nofollow">@endif
    <link rel="icon" type="image/webp" href="/elements/sun.webp"><link rel="stylesheet" href="/app.css?v={{ filemtime(public_path('app.css')) }}">@vite(['resources/css/app.css','resources/js/app.js'])<script src="/app.js?v={{ filemtime(public_path('app.js')) }}" defer></script>
</head>
<body>
<a class="skip" href="#main">Lewati ke isi utama</a>
<div class="top-note">BERTUMBUH DARI RASA INGIN TAHU <span>LES PRIVAT DI {{ mb_strtoupper($site['address']) }}</span></div>
<header class="header">
    <a class="brand" href="/" aria-label="{{ $site['name'] }}, Beranda"><img src="/elements/book.webp" alt="" width="58" height="48"><span>dolanan<span>nalar<span class="brand-dot">.</span></span></span></a>
    <button class="menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="navigation"><span></span><span></span></button>
    <nav id="navigation" aria-label="Navigasi utama">
    @foreach(['/' => 'Beranda','/layanan'=>'Layanan','/tentor'=>'Tentor','/harga'=>'Harga'] as $url=>$label)
        <a href="{{ $url }}" @class(['current'=>request()->getPathInfo()===$url]) @if(request()->getPathInfo()===$url) aria-current="page" @endif>{{ $label }}</a>
    @endforeach
    <a class="nav-parent" href="/perkembangan-anak">{{ auth()->check()?'Dashboard':'Ruang Orang Tua' }} <span aria-hidden="true">↗</span></a>
    </nav>
</header>
<main id="main">@yield('content')</main>
<footer class="footer"><div class="container footer-main"><div><a class="footer-brand" href="/">dolanan nalar.</a><p>{{ $site['slogan'] }}<br>Teman belajar, teman bertumbuh.</p></div><div><h3>Jelajahi</h3><a href="/layanan">Layanan belajar</a><a href="/tentor">Kenalan dengan tentor</a><a href="/harga">Tarif les privat</a></div><div><h3>Mari terhubung</h3><span>{{ $site['address'] }}</span><a href="https://wa.me/{{ $site['whatsapp'] }}">WhatsApp admin ↗</a>@if($site['email'])<a href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a>@endif @if($site['instagram'])<a href="{{ $site['instagram'] }}" rel="noopener noreferrer">Instagram ↗</a>@endif</div></div><div class="container footer-bottom"><span>© {{ date('Y') }} {{ $site['name'] }}</span><span>Dibuat untuk langkah kecil yang berarti.</span></div></footer>
@auth @include('components.logout-dialog') @endauth
</body></html>
