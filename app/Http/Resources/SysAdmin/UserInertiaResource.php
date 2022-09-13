<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:13 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use App\Models\SysAdmin\User;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;


class UserInertiaResource extends JsonResource
{

    /** @noinspection PhpUndefinedFieldInspection */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'id'            => $this->id,
            'username'      => $this->username,
            'name'          => $this->name,
            'userable_type' => $this->userable_type,
            'userable_id'   => $this->userable_id,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
