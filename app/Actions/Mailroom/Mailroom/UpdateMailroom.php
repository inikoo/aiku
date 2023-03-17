<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mailroom\Mailroom;

use App\Actions\WithActionUpdate;
use App\Models\Mailroom\Mailroom;

class UpdateMailroom
{
    use WithActionUpdate;

    public function handle(Mailroom $mailroom, array $modelData): Mailroom
    {
        return $this->update($mailroom, $modelData, ['data']);
    }
}
