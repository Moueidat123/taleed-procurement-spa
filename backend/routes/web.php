<?php

use App\Http\Controllers\SpaController;
use Illuminate\Support\Facades\Route;

/*
| The approved React SPA uses HashRouter (decisions.md D-08): only `/` serves
| its shell. /api, /sanctum, /cp, /pages and /up are handled by Laravel,
| Sanctum and Statamic; Statamic's catch-all registers after these routes.
*/

Route::get('/', SpaController::class)->name('spa');

// Reserve the whole /api namespace: anything outside /api/procurement/v1 is a
// JSON 404 (ApiErrorRenderer), never a Statamic-rendered HTML page.
Route::any('/api/{path?}', fn () => abort(404))->where('path', '.*')->name('api.reserved');
