<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:13 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Workplace\UI;

use App\Models\HumanResources\Workplace;
use Lorisleiva\Actions\Concerns\AsObject;

class GetWorkplaceShowcase
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
