<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 02 Jan 2024 13:21:06 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin\Organisation;

use App\Http\Resources\HasSelfCall;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganisationResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this;

        return [
            'slug' => $organisation->slug,
            'code' => $organisation->code,
            'name' => $organisation->name,
            'logo' => $organisation->logoImageSources(48, 48),
        ];
    }
}
