<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Actions\SysAdmin\User\LogUserRequest;
use App\Http\Resources\SysAdmin\Organisation\OrganisationsResource;
use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class UserShowcaseResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var User $user */
        $user = $this;

        return [
            'id'                      => $user->id,
            'username'                => $user->username,
            'avatar'                  => $user->avatarImageSources(48, 48),
            'email'                   => $user->email,
            'about'                   => $user->about,
            'parent_type'             => $user->parent_type,
            'contact_name'            => $user->contact_name,
            // 'authorizedOrganisations' => OrganisationsResource::collection($user->authorisedOrganisations),
            'authorizedOrganisations' => $user->authorisedOrganisations->map(fn ($organisation) => [
                'slug' => $organisation->slug,
                'name' => $organisation->name,
                'type' => $organisation->type,
            ]),
            'permissions'             => $user->getAllPermissions()->pluck('name')->toArray(),
            'last_active_at'          => $user->stats->last_active_at,
            'last_login'              => [
                'ip'          => $user->stats->last_login_ip,
                'geolocation' => LogUserRequest::make()->getLocation($user->stats->last_login_ip)
            ]
        ];
    }
}
