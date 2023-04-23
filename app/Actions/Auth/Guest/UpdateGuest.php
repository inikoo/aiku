<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

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
