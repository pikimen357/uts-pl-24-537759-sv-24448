<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_mengembalikan_200_saat_db_dan_cache_sehat(): void
    {
        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'healthy')
            ->assertJsonPath('checks.database.status', 'ok')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_mengembalikan_503_dan_menyebut_database_bila_db_mati(): void
    {
        DB::shouldReceive('select')->andThrow(new \RuntimeException('koneksi putus'));

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('status', 'unhealthy')
            ->assertJsonPath('checks.database.status', 'down')
            ->assertJsonPath('checks.cache.status', 'ok');
    }

    public function test_health_mengembalikan_503_dan_menyebut_cache_bila_cache_mati(): void
    {
        Cache::shouldReceive('put')->andThrow(new \RuntimeException('cache putus'));

        $this->getJson('/api/health')
            ->assertStatus(503)
            ->assertJsonPath('checks.cache.status', 'down')
            ->assertJsonPath('checks.database.status', 'ok');
    }
}
