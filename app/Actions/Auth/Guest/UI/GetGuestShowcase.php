<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 25 May 2023 15:03:06 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Auth\Guest\UI;

use App\Models\Auth\Guest;
use Lorisleiva\Actions\Concerns\AsObject;

class GetGuestShowcase
{
    use AsObject;

    public function handle(Guest $guest): array
    {
        return [
            [

            ]
        ];
    }
}
