<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 07 Sept 2022 21:56:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use JsonSerializable;

class HistoryResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'ip_address'     => $this['ip_address'],
            'datetime'       => $this['datetime'],
            'url'            => $this['url'],
            'type'           => $this['type'],
            'tenant'         => app('currentTenant')->slug,
            'old_values'     => $this['old_values'],
            'new_values'     => $this['new_values'],
            'event'          => $this['event'],
            'auditable_id'   => $this['auditable_id'],
            'auditable_type' => $this['auditable_type'],
            'user_id'     => $this['user_id'],
            'user_type'   => $this['user_type'],
            'tags'        => $this['tags']
        ];
    }
}
