<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:11:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachRecurringBillToModel
{
    use AsAction;

    public function handle(PalletDelivery|PalletReturn|Service|Product $parent, $recurringBill): void
    {
        $parent->recurringBills()->attach($recurringBill);
    }
}
