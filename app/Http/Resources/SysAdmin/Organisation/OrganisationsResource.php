<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 15 Mar 2024 14:47:21 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin\Organisation;

use App\Http\Resources\HasSelfCall;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationsResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this;

        return [

            'slug'  => $organisation->slug,
            'name'  => $organisation->name,
            'type'  => $organisation->type,
            'code'  => $organisation->code,


        ];
    }
}
