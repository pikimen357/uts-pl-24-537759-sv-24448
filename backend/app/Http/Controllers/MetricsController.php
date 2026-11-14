<?php

namespace App\Http\Controllers;

use App\Support\Metrics;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __invoke(): Response
    {
        return response(Metrics::render(), 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
