<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BukuApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_akses_tanpa_token_ditolak_401(): void
    {
        $this->getJson('/api/buku')->assertStatus(401);
    }

    public function test_login_berhasil_mengembalikan_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('rahasia123')]);

        $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'rahasia123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_tambah_buku_dengan_token_mengembalikan_201(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/buku', [
            'judul' => 'Laravel Dasar',
            'penulis' => 'Budi',
            'tahun_terbit' => 2024,
            'stok' => 10,
        ])->assertStatus(201);

        $this->assertDatabaseHas('bukus', ['judul' => 'Laravel Dasar']);
    }

    public function test_validasi_gagal_mengembalikan_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/buku', [])->assertStatus(422);
    }

    public function test_tampil_ubah_hapus_buku(): void
    {
        Sanctum::actingAs(User::factory()->create());
        $buku = Buku::factory()->create();

        $this->getJson("/api/buku/{$buku->id}")->assertOk();

        $this->putJson("/api/buku/{$buku->id}", [
            'judul' => 'Judul Baru',
            'penulis' => $buku->penulis,
            'tahun_terbit' => $buku->tahun_terbit,
            'stok' => 5,
        ])->assertOk();

        $this->deleteJson("/api/buku/{$buku->id}")->assertOk();
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
