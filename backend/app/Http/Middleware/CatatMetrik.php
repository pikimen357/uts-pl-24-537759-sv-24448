<?php

namespace App\Http\Middleware;

use App\Support\Metrics;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CatatMetrik
{
    /** Endpoint teknis tidak dihitung agar grafik mencerminkan lalu lintas pengguna. */
    private const DIABAIKAN = ['api/metrics', 'api/health', 'up'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Pola route (api/buku/{buku}), bukan alamat jadi (api/buku/7), supaya jumlah label tetap kecil.
        $route = $request->route()?->uri() ?? 'unmatched';

        if (! in_array($route, self::DIABAIKAN, true)) {
            try {
                Metrics::hit($request->method(), $route, $response->getStatusCode());
            } catch (Throwable) {
                // Gagal mencatat metrik tidak boleh mengganggu respons ke pengguna.
            }
        }

        return $response;
    }
}
