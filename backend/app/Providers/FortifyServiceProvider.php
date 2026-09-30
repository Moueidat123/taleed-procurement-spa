<?php

namespace App\Providers;

use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\GenericPasswordResetLinkResponse;
use App\Models\AppUser;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Fortify;

/**
 * Headless Fortify for application users on the `web` guard (decisions.md D-06).
 */
class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FailedPasswordResetLinkRequestResponse::class, GenericPasswordResetLinkResponse::class);
        $this->app->bind(SuccessfulPasswordResetLinkRequestResponse::class, GenericPasswordResetLinkResponse::class);
    }

    public function boot(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Inactive or unknown accounts fail identically (no enumeration).
        Fortify::authenticateUsing(function (Request $request): ?AppUser {
            $user = AppUser::query()->where('email', Str::lower((string) $request->input('email')))->first();

            if ($user && $user->active && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });

        // Reset links open the approved SPA screen (hash route), not a Fortify view.
        ResetPassword::createUrlUsing(fn (AppUser $user, string $token): string => rtrim((string) config('app.url'), '/')
            .'/#/reset-password?'.http_build_query(['token' => $token, 'email' => $user->email]));

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)
            ->by(Str::lower((string) $request->input('email')).'|'.$request->ip()));

        RateLimiter::for('two-factor', fn (Request $request) => Limit::perMinute(5)
            ->by((string) $request->session()->get('login.id')));
    }
}
