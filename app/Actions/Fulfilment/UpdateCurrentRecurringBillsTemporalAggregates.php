<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Jan 2025 22:17:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment;

use App\Actions\Fulfilment\RecurringBill\CalculateRecurringBillTemporalAggregates;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\RecurringBill;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCurrentRecurringBillsTemporalAggregates
{
    use AsAction;

    public string $commandSignature = 'current_recurring_bills:update_temporal_aggregates';


    public function handle(): void
    {
        foreach (RecurringBill::where('status', RecurringBillStatusEnum::CURRENT)->get() as $recurringBill) {
            CalculateRecurringBillTemporalAggregates::dispatch($recurringBill);
        }


    }




}
