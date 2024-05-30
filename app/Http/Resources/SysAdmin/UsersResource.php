<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 30 May 2024 08:37:52 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Http\Resources\HumanResources\EmployeeResource;
use App\Http\Resources\SysAdmin\Group\GroupResource;
use App\Http\Resources\SysAdmin\Organisation\UserOrganisationResource;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UsersResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var User $user */
        $user = $this;

        return [
            'id'            => $user->id,
            'username'      => $user->username,
            'image'         => $user->imageSources(48, 48),
            'email'         => $user->email,
            'about'         => $user->about,
            'status'        => match ($user->status) {
                true => [
                    'tooltip' => __('active'),
                    'icon'    => 'fal fa-check',
                    'class'   => 'text-green-500'
                ],
                default => [
                    'tooltip' => __('suspended'),
                    'icon'    => 'fal fa-times',
                    'class'   => 'text-red-500'
                ]
            },
            'parent_type'   => $user->parent_type,
            'contact_name'  => $user->contact_name,
            'parent'        => $this->when($this->relationLoaded('parent'), function () {
                return match (class_basename($this->resource->parent)) {
                    'Employee' => new EmployeeResource($this->resource->parent),
                    'Guest'    => new GuestResource($this->resource->parent),
                    default    => [],
                };
            }),
            'group'         => GroupResource::make($user->group),
            'organisations' => UserOrganisationResource::collectionForUser($user->authorisedOrganisations, $this->resource),
            'created_at'    => $user->created_at,
            'updated_at'    => $user->updated_at,
            'roles'         => $user->getRoleNames()->toArray(),
            'permissions'   => $user->getAllPermissions()->pluck('name')->toArray()
        ];
    }
}
