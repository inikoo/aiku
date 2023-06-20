<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Http\Resources\HumanResources\EmployeeResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property mixed $avatar_id
 */
class UserResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var \App\Models\Auth\User $user */
        $user = $this;
        return [
            'id'                 => $user->id,
            'username'           => $user->username,
            'avatar'             => $this->avatar_id,
            'parent_type'        => $user->parent_type,
            'contact_name'       => $user->contact_name,
            'parent'             => $this->when($this->relationLoaded('parent'), function () {
                return match (class_basename($this->resource->parent)) {
                    'Employee' => new EmployeeResource($this->resource->parent),
                    'Guest'    => new GuestResource($this->resource->parent),
                    default    => [],
                };
            }),
            'roles'              => $user->getRoleNames(),
            'direct-permissions' => $user->getDirectPermissions(),
            'permissions'        => $user->getAllPermissions()->pluck('name'),
            'created_at'         => $user->created_at,
            'updated_at'         => $user->updated_at,
        ];
    }
}
