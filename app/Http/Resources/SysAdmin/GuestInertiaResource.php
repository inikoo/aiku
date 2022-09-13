<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 27 Jan 2022 21:54:29 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\SysAdmin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;


class GuestInertiaResource extends JsonResource
{

    /** @noinspection PhpUndefinedFieldInspection */
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'id'     => $this->id,
            'code'   => $this->code,
            'status' => $this->status,
            'name'   => $this->name
        ];
    }
}
