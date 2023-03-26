<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Mar 2023 16:50:28 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mail\Mailroom;

use App\Models\Mail\Mailroom;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMailroom
{
    use AsAction;

    public function handle(array $modelData): Mailroom
    {
        $mailroom = Mailroom::create($modelData);
        $mailroom->stats()->create();

        return $mailroom;
    }
}
