<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Validasi gagal: catat NAMA field yang salah saja, tidak pernah nilainya.
        $exceptions->render(function (ValidationException $e, Request $request) {
            Log::warning('Validasi gagal', [
                'actor' => $request->user() ? 'user:'.$request->user()->id : 'anonymous',
                'action' => $request->method().' /'.$request->path(),
                'reason' => 'input_tidak_valid',
                'invalid_fields' => array_keys($e->errors()),
            ]);

            return null; // lanjutkan ke respons 422 bawaan Laravel
        });

        // Basis data tak terjangkau / query gagal. Log default Laravel memuat SQL
        // beserta binding (bisa berisi email), jadi diganti log yang sudah disaring.
        $exceptions->report(function (QueryException $e) {
            $request = request();
            // Jangan panggil $request->user(): itu sendiri query ke DB yang sedang bermasalah.
            Log::error('Gagal mengakses basis data', [
                'actor' => $request->bearerToken() ? 'authenticated_request' : 'anonymous',
                'action' => $request->method().' /'.$request->path(),
                'reason' => 'db_error_'.($e->getCode() ?: 'unknown'),
                'exception' => class_basename($e),
            ]);

            return false; // hentikan pelaporan default (yang memuat SQL + binding)
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
