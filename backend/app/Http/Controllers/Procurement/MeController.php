<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Resources\CurrentUserResource;
use App\Models\AppUser;
use Illuminate\Http\Request;

class MeController
{
    public function __invoke(Request $request): CurrentUserResource
    {
        /** @var AppUser $user */
        $user = $request->user();

        return new CurrentUserResource($user);
    }
}
