<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetJobPositionsOrganisationData
{
    use AsAction;

    public function handle(Employee|User|Guest $employee, Organisation $organisation): object
    {
        return (object) $employee->jobPositions->map(function ($jobPosition) use ($organisation) {
            $scopes = collect($jobPosition->pivot->scopes)->mapWithKeys(function ($scopeIds, $scope) use ($jobPosition, $organisation) {
                return match ($scope) {
                    'Warehouse' => [
                        'warehouses' => $organisation->warehouses->whereIn('id', $scopeIds)->pluck('slug')->toArray()
                    ],
                    'Shop' => [
                        'shops' => $organisation->shops->whereIn('id', $scopeIds)->pluck('slug')->toArray()
                    ],
                    'Fulfilment' => [
                        'fulfilments' => $organisation->fulfilments->whereIn('id', $scopeIds)->pluck('slug')->toArray()
                    ],
                    default => []
                };
            });

            return [$jobPosition->code => $scopes->toArray()];
        })->reduce(function ($carry, $item) {
            return array_merge_recursive($carry, $item);
        }, []);
    }
}
