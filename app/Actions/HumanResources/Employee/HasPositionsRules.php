<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 22:15:37 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee;

use Illuminate\Support\Arr;

trait HasPositionsRules
{
    public function preparePositionsForValidation(): void
    {
        if ($this->get('positions') and !$this->asAction) {
            $newData = [];
            foreach ($this->get('positions') as $key => $position) {
                $newData[] = match (Arr::get(explode('-', $key), 0)) {
                    'wah', 'dist', 'ful', 'web', 'mrk', 'cus', 'shk' => [
                        'slug'   => $key,
                        'scopes' => array_map(function ($scope) {
                            return [
                                'slug' => $scope
                            ];
                        }, $position)
                    ],

                    default => [
                        'slug'   => $key,
                        'scopes' => []
                    ]
                };
            }

            $positions = [
                'positions' => $newData
            ];

            $this->fill($positions);
        }
    }

}
