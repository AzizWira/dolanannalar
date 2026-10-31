@extends('emails.layout')
@section('title', 'Kabar belajar terbaru — Dolanan Nalar')
@section('preheader', 'Ada pembaruan pertemuan les. Buka ruang orang tua untuk membaca laporannya.')
@section('label', 'KABAR DARI RUANG BELAJAR')
@section('heading', 'Ada cerita belajar terbaru.')
@section('content')
<p style="margin:0 0 14px;">Halo Ayah dan Bunda,</p>
<p style="margin:0;">Tentor telah menerbitkan laporan atau pembaruan pertemuan les. Mari lihat catatan terbaru dan ikuti setiap langkah perkembangan anak melalui ruang orang tua.</p>
@endsection
@section('action', 'Buka laporan di website')
@section('note')Laporan hanya dapat dibuka setelah masuk dengan akun orang tua yang terhubung dengan anak. Demi privasi, rincian perkembangan dan lampiran tetap tersimpan di website.@endsection
