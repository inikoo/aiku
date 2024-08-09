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

/**
 * @property int $number_employees_state_working
 * @property int $number_shops_state_open
 * @property mixed $number_customers
 */
class OrganisationsResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Organisation $organisation */
        $organisation = $this;

        return [
            'id'                                => $organisation->id,
            'slug'                              => $organisation->slug,
            'name'                              => $organisation->name,
            'type'                              => $organisation->type,
            'code'                              => $organisation->code,
            'type_label'                        => $organisation->type->labels()[$organisation->type->value],
            'type_icon'                         => $organisation->type->typeIcon()[$organisation->type->value],
            'number_employees_state_working'    => $this->number_employees_state_working,
            'number_shops_state_open'           => $this->number_shops_state_open,
            'number_customers'                  => $this->number_customers,
            'number_job_positions'              => $organisation->humanResourcesStats->number_job_positions,
        ];
    }
}
