<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mailroom\Outbox;

use App\Actions\WithActionUpdate;
use App\Models\Mail\Outbox;

class UpdateOutbox
{
    use WithActionUpdate;

    public function handle(Outbox $outbox, array $modelData): Outbox
    {
        return $this->update($outbox, $modelData, ['data']);
    }
}
