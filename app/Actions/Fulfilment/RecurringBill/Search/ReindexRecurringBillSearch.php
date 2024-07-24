<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:42:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\Search;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Support\Collection;

class ReindexRecurringBillSearch extends HydrateModel
{
    public string $commandSignature = 'recurring-bill:search {organisations?*} {--s|slugs=}';


    public function handle(RecurringBill $recurringBill): void
    {
        RecurringBillRecordSearch::run($recurringBill);
    }


    protected function getModel(string $slug): RecurringBill
    {
        return RecurringBill::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return RecurringBill::get();
    }
}
