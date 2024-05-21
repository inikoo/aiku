<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 May 2024 17:29:22 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

 namespace App\Actions\Manufacturing\ManufactureTask\UI;

use App\Models\Manufacturing\ManufactureTask;
use App\Models\Manufacturing\RawMaterial;
use Lorisleiva\Actions\Concerns\AsObject;

class GetManufactureTaskShowcase
{
    use AsObject;

    public function handle(ManufactureTask $manufactureTask): array
    {
        return [

        ];
    }
}
