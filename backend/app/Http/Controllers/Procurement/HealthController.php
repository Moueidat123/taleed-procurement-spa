<?php

namespace App\Http\Controllers\Procurement;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Readiness: the application can reach its database. Reports no hostnames,
 * versions of infrastructure or error details.
 */
class HealthController
{
    public function __invoke(): JsonResponse
    {
        try {
            DB::select('select 1');
            $database = 'ok';
        } catch (Throwable) {
            $database = 'unavailable';
        }

        $ok = $database === 'ok';

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'checks' => ['database' => $database],
            'release' => config('app.release'),
        ], $ok ? 200 : 503, ['Cache-Control' => 'no-store']);
    }
}
