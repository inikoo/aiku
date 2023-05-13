<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 01:52:35 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GroupAddress;

use App\Models\Helpers\GroupAddress;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGroupAddress
{
    use AsAction;

    public function handle($data): GroupAddress
    {
        return GroupAddress::create($data);
    }
}
