<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 22:15:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Traits;

use App\Models\HumanResources\JobPosition;
use Illuminate\Support\Arr;

trait HasEmployeePositionGenerator
{
    public function generatePositions(array $modelData): array
    {
        $jobPositions = [];
        foreach (Arr::get($modelData, 'positions', []) as $positionData) {
            /** @var JobPosition $jobPosition */
            $jobPosition                    = $this->organisation->jobPositions()->firstWhere('slug', $positionData['slug']);
            $jobPositions[$jobPosition->id] = [];

            foreach (Arr::get($positionData, 'scopes', []) as $key => $scopes) {
                $scopeData = match ($key) {
                    'shops' => [
                        'Shop' => $this->organisation->shops->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'warehouses' => [
                        'Warehouse' => $this->organisation->warehouses->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
                    ],
                    'fulfilments' => [
                        'Fulfilment' => $this->organisation->fulfilments->whereIn('slug', $scopes['slug'])->pluck('id')->toArray()
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
