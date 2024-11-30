<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 01 Oct 2024 10:50:08 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Traits;

use Illuminate\Support\Arr;

trait WithPreparePositionsForValidation
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
