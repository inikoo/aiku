<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 15 Jun 2024 00:14:45 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use Lorisleiva\Actions\Concerns\AsAction;

class DetachRecurringBillFromModel
{
    use AsAction;



    public function handle($parent, $recurringBill)
    {
        $parent->recurringBills()->detach($recurringBill);
    }

    public function asController($parent, $recurringBill)
    {
        return $this->handle($parent, $recurringBill);
    }
}
