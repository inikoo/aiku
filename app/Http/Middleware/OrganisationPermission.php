<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 00:41:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Middleware;

use App\Models\SysAdmin\User;
use Closure;
use Illuminate\Http\Request;

class OrganisationPermission
{

    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user=auth()->user();
        if(!empty($user) and $user->organisation_id )  {
            setPermissionsTeamId($user->organisation_id);
        }
        return $next($request);
    }
}
