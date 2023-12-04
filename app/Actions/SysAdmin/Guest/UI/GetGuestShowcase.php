<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:23:12 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Guest\UI;

use App\Models\SysAdmin\Guest;
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
