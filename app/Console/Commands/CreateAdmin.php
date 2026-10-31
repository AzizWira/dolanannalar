<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    protected $signature = 'dolanan:admin';

    protected $description = 'Buat akun super admin dengan input interaktif';

    public function handle(): int
    {
        $name = $this->ask('Nama pengelola');
        $email = strtolower(trim($this->ask('Email')));
        $password = $this->secret('Password (minimal 10 karakter, huruf dan angka)');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $name || strlen($password) < 10 || ! preg_match('/[a-zA-Z]/', $password) || ! preg_match('/[0-9]/', $password)) {
            $this->error('Input belum sesuai.');

            return 1;
        }
        if (User::where('email', $email)->exists()) {
            $this->error('Email sudah digunakan. Tidak ada akun diubah.');

            return 1;
        }
        $u = new User;
        $u->forceFill(['name' => $name, 'email' => $email, 'password' => $password, 'role' => 'admin', 'email_verified_at' => now(), 'active' => true])->save();
        $this->info('Akun super admin dibuat.');

        return 0;
    }
}
