<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! config('dolanan.demo') || app()->environment('production')) {
            throw new \RuntimeException('Data demo hanya boleh dibuat saat APP_DEMO=true di lingkungan nonproduksi.');
        }
        $password = env('DEMO_PASSWORD');
        if (! $password || strlen($password) < 10) {
            throw new \RuntimeException('Isi DEMO_PASSWORD minimal 10 karakter di .env lokal.');
        }
        $make = function ($email, $name, $role) use ($password) {
            $u = User::firstOrNew(['email' => $email]);
            $u->forceFill(['name' => $name, 'role' => $role, 'password' => $password, 'active' => true, 'email_verified_at' => now(), 'is_demo' => true]);
            $u->save();

            return $u;
        };
        $make('admin@dolanan.test', 'Pengelola Contoh', 'admin');
        $make('admin2@dolanan.test', 'Pengelola Kedua Contoh', 'admin');
        $parent = $make('orangtua@dolanan.test', 'Bunda Contoh', 'parent');
        $tutors = collect();
        foreach (['Nadia', 'Dimas', 'Laras', 'Raka'] as $i => $name) {
            $t = $make($i === 0 ? 'tentor@dolanan.test' : 'tentor'.($i + 1).'@dolanan.test', 'Kak '.$name.' (Contoh)', 'tutor');
            $t->forceFill(['public_profile' => true, 'subjects' => ['Matematika · SD', 'Matematika · SMP', 'Bahasa · SD', 'Pendampingan TK & SD'][$i], 'bio' => 'Profil ilustratif untuk preview. Foto, nama, dan deskripsi tentor asli akan diperbarui oleh pengelola.'])->save();
            $tutors->push($t);
        }
        foreach (['Alya Contoh', 'Bima Contoh', 'Citra Contoh', 'Danu Contoh', 'Eka Contoh'] as $i => $name) {
            $s = Student::firstOrCreate(['name' => $name], ['grade' => ['SD kelas 3', 'SD kelas 1', 'SMP kelas 7', 'TK', 'SD kelas 5'][$i], 'school' => 'Sekolah Contoh Surakarta']);
            $s->guardians()->firstOrCreate(['email' => $i < 2 ? $parent->email : 'wali'.$i.'@dolanan.test']);
            $t = $tutors[$i % 4];
            $s->assignments()->firstOrCreate(['user_id' => $t->id, 'ended_at' => null], ['type' => 'primary']);
            if ($s->reports()->exists()) {
                continue;
            }
            foreach ([12, 9, 6, 3] as $j => $days) {
                $s->reports()->create(['user_id' => $t->id, 'lesson_date' => now()->subDays($days)->toDateString(), 'subject' => 'Matematika', 'material' => ['Mengenal nilai tempat dan bilangan', 'Penjumlahan dengan menyimpan', 'Pengurangan dan soal cerita', 'Latihan soal cerita sehari-hari'][$j], 'mastered' => 'Mengidentifikasi informasi penting dan menjelaskan langkah pengerjaan.', 'practice' => 'Memeriksa kembali jawaban sebelum menyelesaikan latihan.', 'understanding' => $j > 0 ? 'Baik' : 'Cukup', 'concentration' => $j > 1 ? 'Baik' : 'Cukup', 'problem_solving' => $j > 2 ? 'Baik' : ($j === 0 ? 'Perlu Latihan' : 'Cukup'), 'notes' => 'Ini catatan contoh. Anak mulai berani menjelaskan cara berpikirnya. Untuk latihan di rumah, coba tiga soal cerita sederhana dan diskusikan langkahnya bersama.', 'status' => 'published', 'published_at' => now()->subDays($days), 'revision' => 1, 'grade_snapshot' => $s->grade, 'school_snapshot' => $s->school]);
            }
        }
    }
}
