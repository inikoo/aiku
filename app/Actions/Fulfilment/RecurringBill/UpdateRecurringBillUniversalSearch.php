<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jul 2024 20:57:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill;

use App\Actions\Fulfilment\RecurringBill\Hydrators\RecurringBillHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Support\Collection;

class UpdateRecurringBillUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'recurring-bill:search {organisations?*} {--s|slugs=}';


    public function handle(RecurringBill $recurringBill): void
    {
        RecurringBillHydrateUniversalSearch::run($recurringBill);
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
