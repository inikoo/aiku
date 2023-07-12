<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 14 May 2023 01:55:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\GroupAddress;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\GroupAddress;

class UpdateGroupAddress
{
    use WithActionUpdate;

    public function handle(GroupAddress $address, array $modelData): GroupAddress
    {
        return $this->update($address, $modelData);
    }
}
