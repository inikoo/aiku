<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 22:15:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Traits;

use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;

trait HasEmployeePositionGenerator
{
    public function generatePositions(Organisation $organisation, array $modelData): array
    {
        $jobPositions = [];
        foreach (Arr::get($modelData, 'positions', []) as $positionData) {
            /** @var JobPosition $jobPosition */
             if(in_array($positionData['slug'], [
                 'group_admin',
                 'group_sysadmin',
                 'group_procurement'
             ])){
                 $jobPosition                    = $organisation->group->jobPositions()->firstWhere('code', $positionData['slug']);

             }else{
                 $jobPosition                    = $organisation->jobPositions()->firstWhere('code', $positionData['slug']);
             }


            $jobPositions[$jobPosition->id] = [];
            foreach (Arr::get($positionData, 'scopes', []) as $key => $scopes) {
                $scopeData = match ($key) {
                    'shops' => [
                        'Shop' => $organisation->shops->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'warehouses' => [
                        'Warehouse' => $organisation->warehouses->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'fulfilments' => [
                        'Fulfilment' => $organisation->fulfilments->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    default => []
                };

                if (isset($jobPositions[$jobPosition->id])) {
                    $jobPositions[$jobPosition->id] = array_merge_recursive($jobPositions[$jobPosition->id], $scopeData);
                } else {
                    $jobPositions[$jobPosition->id] = $scopeData;
                }
            }
        }

        foreach ($jobPositions as $id => $scopes) {
            foreach ($scopes as $scopeKey => $ids) {
                $jobPositions[$id][$scopeKey] = array_values(array_unique($ids));
            }
        }

        return $jobPositions;
    }
}
