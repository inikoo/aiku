<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 06 Oct 2023 08:55:04 Malaysia Time, Office, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Helpers\Snapshot;

use App\Actions\Traits\WithActionUpdate;
use App\Models\Helpers\Snapshot;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateSnapshot
{
    use AsAction;
    use WithActionUpdate;

    public function handle(Snapshot $snapshot, array $modelData): Snapshot
    {
        $this->update($snapshot, $modelData, ['layout']);

        return $snapshot;
    }
}
