# Keluarga ilustrasi Dolanan Nalar

Dibuat menggunakan built-in ImageGen. Gaya: ilustrasi editorial kolase kertas dengan grain cetak halus; warna hijau hutan `#174D3C`, hijau `#349E70`, krem `#F7F2D8`, biru muda `#A8E1F4`, kuning `#F3D665`, dan aksen coral `#F35B69`.

Tidak menggunakan emoji sebagai pengganti ilustrasi. Elemen dekoratif tidak menyampaikan data penting, sehingga memakai alt kosong pada website. Ilustrasi bermakna memiliki teks alternatif.

## Prompt set dan penggunaan

| File web | Arahan prompt aset | Penggunaan |
| --- | --- | --- |
| `public/illustrations/belajar-di-rumah.webp` | Tentor perempuan Indonesia dan dua anak SD belajar di meja rendah di rumah Surakarta, buku dan balok angka, jendela lengkung, tanaman, komposisi 3:2 tanpa teks. | Hero Beranda |
| `public/illustrations/tumbuh-bersama.webp` | Buku terbuka, balok geometris, pensil biru, tunas bertumbuh, bintang kertas kuning, still life krem dengan ruang lega. | Layanan, autentikasi, dashboard |
| `public/illustrations/bermain-angka.webp` | Sempoa hijau, manik kuning/coral, balok biru-hijau, buku latihan dan pensil, tanpa teks. | Layanan numerasi |
| `public/illustrations/ruang-orang-tua.webp` | Ibu Indonesia dan anak perempuan melihat laptop biru bersama di rumah, tanaman dan matahari, ekspresi hangat. | Portal orang tua |
| `public/illustrations/jelajah-cerita.webp` | Buku bergambar burung/matahari, tumpukan buku cerita, bookmark coral, burung origami dan tanaman. | Layanan literasi |
| `public/illustrations/kebun-nalar.webp` | Latar hijau hutan dengan daun berlapis di sisi/bawah, bentuk geometris/bintang kuning-biru, bagian tengah atas kosong untuk teks. | Latar CTA/footer |
| `public/elements/sun.webp` | Satu matahari kuning dengan sinar potongan kertas dan wajah kecil ceria, alpha transparan. | Hero, harga |
| `public/elements/cloud.webp` | Satu awan biru muda asimetris, tiga lobus, highlight krem, tanpa wajah, alpha transparan. | Portal orang tua |
| `public/elements/leaf.webp` | Satu tangkai lengkung dengan lima daun hijau lebar, urat krem, alpha transparan. | Hero, sidebar |
| `public/elements/flower.webp` | Satu bunga coral enam kelopak, pusat kuning, tangkai hijau dan dua daun, alpha transparan. | Judul, profil contoh |
| `public/elements/pencil.webp` | Satu pensil kayu kuning diagonal, ujung hijau gelap, ferrule biru, penghapus coral, alpha transparan. | Pendekatan belajar, catatan tentor |
| `public/elements/book.webp` | Satu buku terbuka sampul hijau, halaman krem, bentuk lanskap biru dan bintang kuning tanpa huruf, alpha transparan. | Identitas sementara dan empty state |

Prompt seluruh aset memakai batas: tanpa watermark, tanpa logo pihak lain, tanpa 3D, tanpa emoji, tanpa teks kecuali konsep logo sementara. Transparansi elemen diperiksa melalui alpha channel. Resize/ekspor WebP mempertahankan isi dan alpha; tidak menghapus atau mengganti elemen visual.

Konsep logo awal disimpan sebagai `public/illustrations/logo-sementara.png` dan `.webp`, dengan teks Dolanan Nalar serta slogan. Navbar saat ini memakai wordmark tipografis dan elemen buku agar tetap terbaca pada ukuran kecil; logo final belum ditetapkan customer.

Sumber PNG ilustrasi tersedia di `public/illustrations/*.png`. Sumber PNG keenam elemen tersedia di `design/element-originals/`. Aset web elemen berukuran maksimum 600 px; ilustrasi maksimum 1400 × 1000 px. Tidak memerlukan layanan generasi gambar saat website digunakan.

Font lokal: Bricolage Grotesque dan Plus Jakarta Sans dari paket Fontsource, dengan lisensi SIL OFL disertakan di `public/fonts/`. File font tidak diambil dari Google saat halaman dibuka.
