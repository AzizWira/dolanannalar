<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="color-scheme" content="light"><title>@yield('title', 'Kabar dari Dolanan Nalar')</title></head>
<body style="margin:0;padding:0;background-color:#f8f4e3;color:#163f33;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;font-size:1px;line-height:1px;max-height:0;max-width:0;opacity:0;overflow:hidden;">@yield('preheader')</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f4e3;"><tr><td align="center" style="padding:32px 16px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;">
<tr><td style="padding:0 8px 20px;font-size:13px;letter-spacing:2px;font-weight:bold;color:#174d3c;">TEMAN BELAJAR, TEMAN BERTUMBUH.</td></tr>
<tr><td style="background-color:#174d3c;border-radius:24px 24px 0 0;padding:30px 28px;">
<p style="margin:0 0 20px;color:#fffdf5;font-size:28px;line-height:1.2;font-weight:bold;letter-spacing:-1px;">{{ $site['name'] }}<span style="color:#f3d665;">.</span></p>
<span style="display:inline-block;background-color:#f3d665;color:#174d3c;padding:7px 12px;border-radius:20px;font-size:11px;letter-spacing:1px;font-weight:bold;">@yield('label')</span>
</td></tr>
<tr><td style="background-color:#fffdf5;padding:32px 28px 28px;border-left:1px solid #d6dccb;border-right:1px solid #d6dccb;">
<h1 style="margin:0 0 22px;font-size:30px;line-height:1.2;letter-spacing:-.8px;color:#174d3c;">@yield('heading')</h1>
<div style="font-size:15px;line-height:1.8;color:#486153;">@yield('content')</div>
<table role="presentation" cellpadding="0" cellspacing="0" style="margin:26px 0;"><tr><td bgcolor="#174d3c" style="border-radius:12px;"><a href="{{ $url }}" style="display:inline-block;padding:16px 22px;color:#ffffff;text-decoration:none;font-size:14px;line-height:1.4;font-weight:bold;border:1px solid #174d3c;border-radius:12px;">@yield('action')</a></td></tr></table>
<div style="padding:16px 18px;background-color:#edf3e8;border-radius:12px;font-size:13px;line-height:1.7;color:#486153;">@yield('note')</div>
<p style="margin:26px 0 8px;font-size:12px;line-height:1.7;color:#586d61;">Jika tombol tidak terbuka, salin tautan ini ke browser:</p>
<p style="margin:0;font-size:12px;line-height:1.7;word-break:break-all;overflow-wrap:anywhere;"><a href="{{ $url }}" style="color:#174d3c;text-decoration:underline;">{{ $url }}</a></p>
</td></tr>
<tr><td style="padding:24px 28px;background-color:#a8e1f4;border-radius:0 0 24px 24px;">
<p style="margin:0 0 6px;font-size:18px;font-weight:bold;color:#174d3c;">{{ $site['slogan'] }}</p><p style="margin:0;font-size:12px;line-height:1.7;color:#174d3c;">Langkah kecil hari ini, bekal besar nanti.</p></td></tr>
<tr><td align="center" style="padding:22px 12px 0;font-size:11px;line-height:1.8;color:#586d61;">Email otomatis dari {{ $site['name'] }} · {{ $site['address'] }}<br>© {{ date('Y') }} {{ $site['name'] }}</td></tr>
</table></td></tr></table></body></html>
