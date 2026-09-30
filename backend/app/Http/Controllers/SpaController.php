<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * Serves the compiled SPA shell (root `npm run build:backend` → public/spa).
 * In local development the HTTPS proxy sends `/` to the Vite dev server instead.
 */
class SpaController
{
    public function __invoke(): Response
    {
        $shell = public_path('spa/index.html');

        abort_unless(is_file($shell), 503, 'The SPA has not been built.');

        return response((string) file_get_contents($shell), 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-cache, private',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'same-origin',
            'X-Frame-Options' => 'DENY',
        ]);
    }
}
