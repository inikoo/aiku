<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 11 Jul 2023 12:31:59 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property mixed $avatar_id
 */
class UserSearchResultResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var \App\Models\Auth\User $user */
        $user = $this;
        return [
            'username'           => $user->username,
            'avatar'             => $this->avatar_id,
            'email'              => $user->email,
            'contact_name'       => $user->contact_name,
            'route'              => [
                'name'       => 'sysadmin.users.index',
                'parameters' => $user->username
            ],
            'icon'   => ['fal', 'fa-terminal']
        ];
    }
}
