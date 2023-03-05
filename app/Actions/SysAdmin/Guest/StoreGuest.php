<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\SysAdmin\Guest;

use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGuest
{
    use AsAction;

    public function handle(array $modelData): Guest
    {
        return Guest::create($modelData);
    }
}
