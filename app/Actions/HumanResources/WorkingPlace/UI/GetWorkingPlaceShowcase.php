<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:30:45 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\WorkingPlace\UI;

use App\Models\HumanResources\Workplace;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWorkingPlaceShowcase
{
    use AsObject;

    public function handle(Workplace $workplace): array
    {
        return [
            [

            ]
        ];
    }
}
