<?php

namespace Tests\Feature;

use Tests\TestCase;

class MetricsTest extends TestCase
{
    public function test_metrics_memuat_hitungan_request_dengan_label_route_pola(): void
    {
        // Login tanpa data -> 422, ikut terhitung.
        $this->postJson('/api/login', [])->assertStatus(422);

        $this->get('/api/metrics')
            ->assertOk()
            ->assertSee('# TYPE http_requests_total counter', false)
            ->assertSee('http_requests_total{method="POST",route="api/login",status="422"}', false);
    }
}
