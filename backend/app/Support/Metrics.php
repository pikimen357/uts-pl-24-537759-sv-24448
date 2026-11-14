<?php

namespace App\Support;

/**
 * Penghitung request sederhana untuk Prometheus (tanpa library tambahan).
 * Label hanya berisi kategori yang jumlahnya tetap: method, pola route, status.
 * Alamat lengkap, ID, atau data pengguna TIDAK dijadikan label.
 */
class Metrics
{
    private static function path(): string
    {
        return storage_path('framework/metrics.json');
    }

    public static function hit(string $method, string $route, int $status): void
    {
        $fp = @fopen(self::path(), 'c+');
        if (! $fp) {
            return;
        }

        try {
            if (! flock($fp, LOCK_EX)) {
                return;
            }

            $data = json_decode((string) stream_get_contents($fp), true);
            if (! is_array($data)) {
                $data = [];
            }

            $key = $method."\t".$route."\t".$status;
            $data[$key] = ($data[$key] ?? 0) + 1;

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }
    }

    public static function render(): string
    {
        $data = [];
        if (is_file(self::path())) {
            $decoded = json_decode((string) @file_get_contents(self::path()), true);
            $data = is_array($decoded) ? $decoded : [];
        }

        $out = "# HELP http_requests_total Jumlah request HTTP yang diterima aplikasi\n";
        $out .= "# TYPE http_requests_total counter\n";

        ksort($data);
        foreach ($data as $key => $count) {
            [$method, $route, $status] = explode("\t", $key);
            $out .= sprintf(
                "http_requests_total{method=\"%s\",route=\"%s\",status=\"%s\"} %d\n",
                self::esc($method),
                self::esc($route),
                self::esc($status),
                $count
            );
        }

        return $out;
    }

    private static function esc(string $v): string
    {
        return str_replace(['\\', '"', "\n"], ['\\\\', '\\"', '\\n'], $v);
    }
}
