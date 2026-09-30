<?php

namespace App\Actions\Fortify;

use App\Models\AppUser;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    /**
     * Mirrors the approved SPA rule (Auth.tsx): 12+ characters with upper, lower and digit.
     *
     * @param  array<string, string>  $input
     */
    public function reset(AppUser $user, array $input): void
    {
        Validator::make($input, [
            'password' => ['required', 'string', Password::min(12)->mixedCase()->numbers(), 'confirmed'],
        ])->validate();

        $user->forceFill(['password' => $input['password']])->save();
    }
}
