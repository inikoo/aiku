<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 27 Jun 2023 14:15:16 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Auth;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class GroupUserResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var \App\Models\Auth\GroupUser $groupUser */
        $groupUser = $this;

        return [
            'id'       => $groupUser->id,
            'username' => $groupUser->username,
            'name'     => $groupUser->name,
            'email'    => $groupUser->email,
            'status'   => $groupUser->status,
        ];
    }
}
