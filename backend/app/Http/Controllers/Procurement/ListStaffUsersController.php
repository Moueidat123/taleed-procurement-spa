<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Resources\StaffUserResource;
use App\Models\AppUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Super Admin people & access list (openapi-v1.yaml GET /staff/users). Returns
 * every staff member (Analysts and Super Admins) so access can be reviewed in
 * one place. Champions are organization users, not staff, and are excluded.
 */
class ListStaffUsersController
{
    public function __invoke(Request $request): AnonymousResourceCollection
    {
        abort_unless(
            ($user = $request->user()) !== null && $user->role === 'admin' && $user->active,
            403,
        );

        $staff = AppUser::query()
            ->whereIn('role', ['analyst', 'admin'])
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return StaffUserResource::collection($staff);
    }
}
