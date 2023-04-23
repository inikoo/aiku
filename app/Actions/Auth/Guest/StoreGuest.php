<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 24 Apr 2023 20:23:18 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Auth\Guest;

use App\Models\Auth\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreGuest
{
    use AsAction;

    public function handle(array $modelData): Guest
    {
        return Guest::create($modelData);
    }
}
