<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 18 Jul 2023 12:46:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\HumanResources;

use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

class EmployeeSearchResultResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        /** @var Employee $employee */
        $employee = $this;

        return [

            'contact_name'        => $employee->contact_name,
            'worker_number'       => $employee->worker_number,
            'state'               => $employee->state,
            'user'                => $employee->user?->only('username', 'status'),
            'route'               => [
                'name'       => 'grp.org.hr.employees.show',
                'parameters' => [
                    'organisation' => $employee->organisation->slug,
                    'employee'     => $employee->slug
                ]
            ],
            'icon'   => ['fal', 'fa-user-hard-hat']
        ];
    }
}
