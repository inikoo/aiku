<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 19 May 2024 12:26:53 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Models\Fulfilment\RecurringBill;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AttachRecurringBillToModel
{
    use AsAction;
    
    

    public function handle($parent, $recurringBill)
    {
        $parent->recurringBills()->attach($recurringBill);
    }

    public function asController($parent , $recurringBill)
    {
        return $this->handle($parent, $recurringBill);
    }
}