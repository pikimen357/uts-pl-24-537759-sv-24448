<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
        ];

        $healthy = collect($checks)->every(fn ($c) => $c['status'] === 'ok');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $healthy ? 200 : 503);
    }

    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            // Query sungguhan ke MySQL, bukan sekadar cek konfigurasi.
            DB::select('SELECT 1');

            return ['status' => 'ok', 'latency_ms' => $this->ms($start)];
        } catch (Throwable $e) {
            Log::error('Health check gagal: database tidak bisa dihubungi', [
                'actor' => 'system:healthcheck',
                'action' => 'health.check.database',
                'reason' => class_basename($e),
                'error' => $this->safeMessage($e),
                'db_host' => config('database.connections.'.config('database.default').'.host'),
            ]);

            return ['status' => 'down', 'error' => class_basename($e)];
        }
    }

    private function checkCache(): array
    {
        $start = microtime(true);
        $key = 'healthcheck:'.Str::random(8);

        try {
            // Tulis lalu baca ulang, supaya benar-benar menguji cache.
            Cache::put($key, 'ok', 10);
            $value = Cache::get($key);
            Cache::forget($key);

            if ($value !== 'ok') {
                throw new \RuntimeException('Nilai cache tidak sesuai setelah ditulis');
            }

            return ['status' => 'ok', 'latency_ms' => $this->ms($start)];
        } catch (Throwable $e) {
            Log::error('Health check gagal: cache tidak berfungsi', [
                'actor' => 'system:healthcheck',
                'action' => 'health.check.cache',
                'reason' => class_basename($e),
                'error' => $this->safeMessage($e),
                'cache_store' => config('cache.default'),
            ]);

            return ['status' => 'down', 'error' => class_basename($e)];
        }
    }

    private function ms(float $start): float
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Pesan error dari driver DB bisa memuat host/username/SQL.
     * Ambil hanya bagian pendek dan buang isi kurung (biasanya SQL + binding).
     */
    private function safeMessage(Throwable $e): string
    {
        $msg = preg_replace('/\(Connection:.*$/s', '', $e->getMessage());

        return Str::limit(trim($msg), 160);
    }
}
