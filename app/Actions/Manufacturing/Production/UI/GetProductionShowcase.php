<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 May 2024 17:29:22 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Manufacturing\Production\UI;

use App\Models\Manufacturing\Production;
use Lorisleiva\Actions\Concerns\AsObject;

class GetProductionShowcase
{
    use AsObject;

    public function handle(Production $production): array
    {
        return [

        ];
    }
}
