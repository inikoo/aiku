<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:13:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Guest;
use App\Models\SysAdmin\User;
use Lorisleiva\Actions\Concerns\AsAction;

class GetJobPositionsGroupData
{
    use AsAction;

    public function handle(Employee|User|Guest $employee, Group $group): array
    {
        return (array) $employee->jobPositions->map(function ($jobPosition) use ($group) {
            $scopes = collect($jobPosition->pivot->scopes)->mapWithKeys(function ($scopeIds, $scope) use ($jobPosition, $group) {
                return match ($scope) {
                    'Warehouse' => [
                        'warehouses' => $group->warehouses->whereIn('id', $scopeIds)->pluck('slug')->toArray()
                    ],
                    'Shop' => [
                        'shops' => $group->shops->whereIn('id', $scopeIds)->pluck('slug')->toArray()
                    ],
                    'Fulfilment' => [
                        'fulfilments' => $group->fulfilments->whereIn('id', $scopeIds)->pluck('slug')->toArray()
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
