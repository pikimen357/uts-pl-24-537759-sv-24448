<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin', 'password' => bcrypt('password')],
        );

        if (Buku::count() === 0) {
            $data = [
                ['judul' => 'Laravel untuk Pemula', 'penulis' => 'Budi Santoso', 'tahun_terbit' => 2023, 'stok' => 10],
                ['judul' => 'Belajar Vue.js', 'penulis' => 'Siti Aminah', 'tahun_terbit' => 2022, 'stok' => 7],
                ['judul' => 'Docker dari Nol', 'penulis' => 'Andi Wijaya', 'tahun_terbit' => 2024, 'stok' => 5],
                ['judul' => 'REST API dengan Sanctum', 'penulis' => 'Rina Kusuma', 'tahun_terbit' => 2024, 'stok' => 12],
                ['judul' => 'Git dan GitHub Actions', 'penulis' => 'Dewi Lestari', 'tahun_terbit' => 2025, 'stok' => 8],
            ];

            foreach ($data as $item) {
                Buku::create($item);
            }
        }
    }
}
