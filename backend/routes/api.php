<?php

use App\Http\Controllers\Procurement\AcceptInvitationController;
use App\Http\Controllers\Procurement\ConfirmVerificationController;
use App\Http\Controllers\Procurement\HealthController;
use App\Http\Controllers\Procurement\IssueInvitationController;
use App\Http\Controllers\Procurement\ListStaffUsersController;
use App\Http\Controllers\Procurement\MeController;
use App\Http\Controllers\Procurement\OrganizationController;
use App\Http\Controllers\Procurement\RegisterController;
use App\Http\Controllers\Procurement\SendVerificationController;
use App\Http\Controllers\Procurement\UpdateOrganizationAccessController;
use App\Http\Controllers\Procurement\UpdateStaffAccessController;
use Illuminate\Support\Facades\Route;

/*
| Business API — prefix /api/procurement/v1 (bootstrap/app.php).
| Application `web` guard only, via Sanctum's stateful SPA mode.
| Fortify auth routes (login, logout, forgot/reset password, two-factor) are
| registered under /api/procurement/v1/auth by config/fortify.php.
| Contract: docs/production/api/openapi-v1.yaml
*/

Route::get('/health', HealthController::class)->name('procurement.health');

// Champion self-registration (D-31): public, throttled, allow-listed fields.
Route::post('/auth/register', RegisterController::class)
    ->middleware('throttle:register')->name('procurement.auth.register');

// Accept a staff invitation (D-10): public, throttled, single-use token.
Route::post('/auth/invitations/accept', AcceptInvitationController::class)
    ->middleware('throttle:invitation')->name('procurement.invitations.accept');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', MeController::class)->name('procurement.auth.me');

    // Six-digit email verification (D-30): throttled send and confirm.
    Route::post('/auth/email/verify/send', SendVerificationController::class)
        ->middleware('throttle:verify')->name('procurement.auth.verify.send');
    Route::post('/auth/email/verify/confirm', ConfirmVerificationController::class)
        ->middleware('throttle:verify')->name('procurement.auth.verify.confirm');

    // Company profile (D-31): one Champion per company; duplicates blocked.
    Route::get('/organization', [OrganizationController::class, 'show'])
        ->name('procurement.organization.show');
    Route::patch('/organization', [OrganizationController::class, 'update'])
        ->name('procurement.organization.update');

    // Staff people & access and organization pause/enable (D-10, D-17): Super
    // Admin only, gated behind confirmed TOTP two-factor for staff.
    Route::middleware('staff.2fa')->group(function () {
        // Staff invitations (D-10): token emailed, never returned.
        Route::post('/staff/invitations', IssueInvitationController::class)
            ->name('procurement.staff.invitations.issue');
        Route::get('/staff/users', ListStaffUsersController::class)
            ->name('procurement.staff.users.index');
        Route::patch('/staff/users/{user}/access', UpdateStaffAccessController::class)
            ->name('procurement.staff.users.access');
        Route::patch('/staff/organizations/{organization}/access', UpdateOrganizationAccessController::class)
            ->name('procurement.staff.organizations.access');
    });
});

// Unknown business routes are JSON 404s, never the SPA shell or a CMS page.
Route::any('/{path}', fn () => abort(404))->where('path', '.*')->name('procurement.fallback');
