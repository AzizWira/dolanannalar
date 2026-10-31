# Dolanan Nalar — Laravel

Implementasi aktif berada di folder ini (`dolanan-nalar/laravel`), bukan starter Vinext pada folder induk. PRD berada di `../docs/PRD.md`.

## Yang tersedia

- Halaman publik terpisah: Beranda, Layanan, Tentor, Harga, dan Perkembangan Anak.
- Enam ilustrasi adegan/komposisi, enam elemen transparan satuan, serta konsep logo sementara. Aset web di `public/illustrations` dan `public/elements`; sumber elemen di `design/element-originals`.
- Font lokal Bricolage Grotesque dan Plus Jakarta Sans beserta lisensi SIL OFL.
- Login email/password, registrasi orang tua, verifikasi email, reset password, integrasi Google Socialite.
- Role super admin, tentor, orang tua; penugasan utama/pengganti; satu orang tua dengan beberapa anak.
- CRUD murid, akun/profil tentor, konten/harga/layanan; tambah layanan dan tarif dari dashboard.
- Laporan per pertemuan, draf/publikasi/pembaruan, tiga kategori penilaian, grafik kategorikal, catatan/tugas, PDF privat.
- Email pemberitahuan melalui queue database, event publikasi dengan token unik, serta retry dan pengecekan ulang hak akses penerima.

## Kebutuhan

PHP 8.3+, Composer, ekstensi PHP standar Laravel termasuk PDO MySQL atau SQLite, mbstring, fileinfo, OpenSSL, ZIP; Node.js yang sesuai lockfile untuk build aset. Produksi menggunakan MySQL; preview dan feature test menggunakan SQLite. Tidak memerlukan Node.js sebagai server produksi.

## Menjalankan lokal

```sh
composer install
npm ci
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
php artisan serve --host=127.0.0.1 --port=8010
```

Pada Windows gunakan `Copy-Item .env.example .env` sebagai pengganti `cp` jika diperlukan. Untuk SQLite, buat file kosong `database/database.sqlite` sebelum migrate. Set `APP_URL=http://127.0.0.1:8010`.

Jalankan worker pada terminal terpisah:

```sh
php artisan queue:work --tries=3 --timeout=90
```

## Data demo

Hanya untuk lingkungan nonproduksi:

```dotenv
APP_ENV=local
APP_DEMO=true
DEMO_PASSWORD=isi_password_demo_minimal_10_karakter
```

```sh
php artisan config:clear
php artisan db:seed
```

Akun: `admin@dolanan.test`, `admin2@dolanan.test`, `tentor@dolanan.test`, `tentor2@dolanan.test` sampai `tentor4@dolanan.test`, serta `orangtua@dolanan.test`. Semua memakai `DEMO_PASSWORD` yang diberikan saat seed. Orang tua demo terhubung dengan Alya Contoh dan Bima Contoh. Seluruh akun/data berlabel contoh; jangan digunakan untuk data anak nyata.

Seeder menolak `APP_ENV=production` dan `APP_DEMO=false`. Seeder tidak mengirim email laporan. Pada preview yang telah disiapkan, password demo lokal tercantum pada catatan pengujian, bukan pada source aplikasi produksi.

## Google Login

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://dolanannalar.com/auth/google/callback
```

Samakan redirect URI di konsol Google dengan `.env`. Google Login pada UI aktif jika Client ID tersedia; route juga memerlukan Client Secret. Google ditujukan untuk akun orang tua. Kepemilikan email harus terverifikasi sebelum akun yang sudah ada dihubungkan. Token Google tidak disimpan sebagai data publik.

Integrasi sudah ditulis; login terhadap Google nyata belum diuji tanpa kredensial. SMTP tidak menggantikan kredensial Google OAuth.

## SMTP dan email

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=host_dari_penyedia
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=alamat_pengirim_terverifikasi
MAIL_FROM_NAME="Dolanan Nalar"
QUEUE_CONNECTION=database
```

Sesuaikan skema/port dengan penyedia; `smtps` lazim untuk implicit TLS pada port 465. Jangan menonaktifkan verifikasi sertifikat. Setelah mengubah `.env`, jalankan `php artisan config:clear` atau cache ulang konfigurasi dan restart worker.

Default lokal menggunakan `MAIL_MAILER=log` agar demo tidak mengirim pesan ke pihak luar. Email verifikasi/reset diproses oleh mailer aplikasi; pemberitahuan laporan diproses queue. Aktifkan dan uji SMTP sebelum digunakan customer.

Email berisi pemberitahuan dan tautan, bukan rincian nilai/PDF. Penerima harus login dan tetap memiliki relasi dengan anak. Worker yang diulang dapat mencoba kembali pengiriman; pengiriman SMTP tidak menjamin exactly-once jika koneksi putus setelah penyedia menerima pesan. Token publikasi dan lock job mencegah duplikasi umum dari klik ulang/concurrency.

## PDF dan foto

PDF: maksimal 3 file per laporan, 5 MB/file. Angka ini merupakan usulan teknis PRD, dapat disesuaikan sebelum peluncuran. File disimpan pada disk `local` di lokasi privat, bukan pada folder public. Unduhan wajib melewati `/lampiran/{id}` dan pemeriksaan izin.

Foto tentor: JPG/PNG/WebP maksimal 2 MB, disajikan melalui `/foto-tentor/{id}` hanya jika akun tentor dan profil publiknya aktif. Tidak memerlukan symlink `storage:link` untuk foto ini. Preview memakai ilustrasi pengganti foto dan label profil contoh.

## Pengujian

```sh
php artisan test --compact
npm run build
composer validate --no-check-publish
```

Pengujian memakai database SQLite in-memory, fake queue/mail/storage untuk skenario terkait. Pengujian tidak menghubungi SMTP atau Google sungguhan. Pemeriksaan MySQL produksi, backup/restore, konfigurasi provider, dan lintas browser pada perangkat fisik tetap diperlukan sebelum peluncuran.

## Deployment ke hosting Laravel

1. Siapkan database MySQL kosong dan direktori aplikasi; arahkan document root domain ke `public/`.
2. Upload source dan aset build; jalankan `composer install --no-dev --optimize-autoloader`. Build dapat dilakukan di lokal/CI dengan `npm ci && npm run build`, lalu unggah `public/build`.
3. Isi `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_DEMO=false`, `APP_URL=https://dolanannalar.com`, koneksi MySQL, Google OAuth, SMTP, serta `SESSION_SECURE_COOKIE=true`.
4. Buat `APP_KEY` sekali; jangan menggantinya pada update rutin. Pastikan `storage` dan `bootstrap/cache` dapat ditulis oleh proses aplikasi.
5. Jalankan `php artisan migrate --force`, kemudian `php artisan dolanan:admin` untuk setiap pengelola. Command memakai prompt password tersembunyi dan menolak menimpa akun yang sudah ada.
6. Konfigurasikan queue worker dengan process manager. Jika shared hosting tidak mendukung daemon, cron dapat menjalankan `php artisan queue:work --stop-when-empty --tries=3 --timeout=90` dengan pencegahan overlap; pastikan kapasitas dan durasinya sesuai hosting.
7. Aktifkan HTTPS, cache konfigurasi/view/route, lalu restart worker setelah update.
8. Ganti data dummy menggunakan database produksi bersih dan konten asli. Jangan menyalin database demo ke produksi.
9. Backup database dan `storage/app/private`, serta foto publik; tetapkan retensi dan uji restore pada lingkungan terpisah.
10. Uji tiga role, Google, email nyata, PDF privat, dan alur laporan sebelum mengarahkan trafik customer. Verifikasi domain di Search Console dan kirim `/sitemap.xml`.

Domain sudah tersedia. Tidak ada pembelian domain, perubahan DNS, deployment produksi, atau pendaftaran Search Console yang dilakukan pada tahap implementasi lokal ini.

## Batas dan catatan

- Tidak ada akun murid, upload jawaban, pembayaran, penjadwalan, absensi, atau modul chat.
- Harga transportasi ditampilkan sebagai informasi; tidak ada kalkulasi rute otomatis.
- Logo, data tentor, dan teks layanan merupakan bahan awal untuk review.
- Revisi UI dan fitur tambahan harus tetap mengikuti PRD.
