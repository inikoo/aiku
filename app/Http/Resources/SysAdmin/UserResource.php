<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;


class UserResource extends JsonResource
{

    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var User $user */
        $user = $this;

        return [
            'id'                 => $user->id,
            'username'           => $user->username,
            'roles'              => $user->getRoleNames(),
            'direct-permissions' => $user->getDirectPermissions(),
            'permissions'        => $user->getAllPermissions()->pluck('name'),
            'created_at'         => $user->created_at,
            'updated_at'         => $user->updated_at,
        ];
    }
}
