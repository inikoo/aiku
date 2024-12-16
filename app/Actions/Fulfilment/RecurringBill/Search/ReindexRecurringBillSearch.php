<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:42:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\Search;

use App\Actions\HydrateModel;
use App\Models\CRM\Customer;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexRecurringBillSearch extends HydrateModel
{
    public string $commandSignature = 'search:recurring_bills {organisations?*} {--s|slugs=}';


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

    protected function loopAll(Command $command): void
    {

        $command->info("Reindex Recurring Bills search");

        $count = RecurringBill::count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();
        RecurringBill::chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
