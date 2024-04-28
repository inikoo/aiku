<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 13:08:19 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\RecurringBill\Hydrators\RecurringBillHydrateTransactions;
use App\Actions\HydrateModel;


use App\Models\Fulfilment\RecurringBill;
use Illuminate\Support\Collection;

class HydrateRecurringBill extends HydrateModel
{
    public string $commandSignature = 'recurring_bill:hydrate {organisations?*} {--s|slugs=}';

    public function handle(RecurringBill $recurringBill): void
    {
        RecurringBillHydrateTransactions::run($recurringBill);
    }

    protected function getModel(string $slug): RecurringBill
    {
        return RecurringBill::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return RecurringBill::all();
    }
}
