<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Mailroom\Mailshot;

use App\Models\Mailroom\Mailshot;
use App\Models\Mailroom\Outbox;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreMailshot
{
    use AsAction;

    public function handle(Outbox $outbox, array $modelData): Mailshot
    {
        $modelData['shop_id']=$outbox->shop_id;
        /** @var Mailshot $mailshot */
        $mailshot = $outbox->mailshots()->create($modelData);
        $mailshot->stats()->create();

        return $mailshot;
    }
}
