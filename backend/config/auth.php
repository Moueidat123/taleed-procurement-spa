<?php

use App\Models\AppUser;

/*
|--------------------------------------------------------------------------
| Authentication — two independent identity domains (decisions.md D-03)
|--------------------------------------------------------------------------
|
| `web`      Procurement application users (Champion, Analyst, Super Admin),
|            Eloquent `app_users`. This is the Laravel default guard and the
|            ONLY guard accepted by the business API (see config/sanctum.php).
|
| `statamic` The single Statamic Core control-panel administrator (file
|            repository). Used only by /cp via config/statamic/users.php.
|
| Each domain has its own password brokers and token tables, so a token or
| email from one provider can never be resolved against the other. Both
| guards share the Laravel session (Statamic docs caveat): invalidating the
| session logs out both, which fails safe.
|
*/

return [

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'app_users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'app_users',
        ],

        'statamic' => [
            'driver' => 'session',
            'provider' => 'statamic',
        ],
    ],

    'providers' => [
        'app_users' => [
            'driver' => 'eloquent',
            'model' => AppUser::class,
        ],

        'statamic' => [
            'driver' => 'statamic',
        ],
    ],

    'passwords' => [
        'app_users' => [
            'provider' => 'app_users',
            'table' => 'app_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'statamic_resets' => [
            'provider' => 'statamic',
            'table' => 'cms_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'statamic_activations' => [
            'provider' => 'statamic',
            'table' => 'cms_password_activation_tokens',
            'expire' => 4320,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
