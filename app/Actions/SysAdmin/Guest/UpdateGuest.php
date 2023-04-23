<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 17 Oct 2022 14:36:00 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest;

use App\Actions\WithActionUpdate;
use App\Models\Auth\Guest;

class UpdateGuest
{
    use WithActionUpdate;

    public function handle(Guest $guest, array $modelData): Guest
    {
        return $this->update($guest, $modelData, [
            'data',
        ]);
    }
}
