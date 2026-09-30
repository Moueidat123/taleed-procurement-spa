<?php

use App\Http\Controllers\Procurement\HealthController;
use App\Http\Controllers\Procurement\MeController;
use Illuminate\Support\Facades\Route;

/*
| Business API — prefix /api/procurement/v1 (bootstrap/app.php).
| Application `web` guard only, via Sanctum's stateful SPA mode.
| Fortify auth routes (login, logout, forgot/reset password, two-factor) are
| registered under /api/procurement/v1/auth by config/fortify.php.
| Contract: docs/production/api/openapi-v1.yaml
*/

Route::get('/health', HealthController::class)->name('procurement.health');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', MeController::class)->name('procurement.auth.me');
});

// Unknown business routes are JSON 404s, never the SPA shell or a CMS page.
Route::any('/{path}', fn () => abort(404))->where('path', '.*')->name('procurement.fallback');
