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


        $rawJobPositions = [];
        foreach (Arr::get($modelData, 'positions', []) as $positionData) {


            /** @var JobPosition $jobPosition */
            if (in_array($positionData['code'], [
                'group_admin',
                'group_sysadmin',
                'group_procurement'
            ])) {
                $jobPosition                    = $organisation->group->jobPositions()->firstWhere('code', $positionData['code']);

            } else {
                $jobPosition                    = $organisation->jobPositions()->firstWhere('code', $positionData['code']);
            }





            $rawJobPositions[$jobPosition->id] = [];
            foreach (Arr::get($positionData, 'scopes', []) as $key => $scopes) {




                $scopeData = match ($key) {
                    'shops' => [
                        'Shop' => $organisation->shops->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'warehouses'=> [
                        'Warehouse' => $organisation->warehouses->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'fulfilments' => [
                        'Fulfilment' => $organisation->fulfilments->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    default => []
                };

                if (isset($rawJobPositions[$jobPosition->id])) {
                    $rawJobPositions[$jobPosition->id] = array_merge_recursive($rawJobPositions[$jobPosition->id], $scopeData);
                } else {
                    $rawJobPositions[$jobPosition->id] = $scopeData;
                }
            }
        }



        $jobPositions=[];
        foreach ($rawJobPositions as $id => $scopes) {

            foreach ($scopes as $scopeKey => $ids) {
                $rawJobPositions[$id][$scopeKey] = array_values(array_unique($ids));
            }

            $jobPositions[$id] = [
                'scopes'=>$rawJobPositions[$id],
                'organisation_id'=>$organisation->id
            ];


        }

        return $jobPositions;
    }
}
