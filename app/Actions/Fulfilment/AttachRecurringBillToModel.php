<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 May 2024 12:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachRecurringBillToModel
{
    use AsAction;

    public function handle(PalletDelivery|PalletReturn|Service|Outer $parent, $recurringBill): void
    {
        $parent->recurringBills()->attach($recurringBill);
    }
}
