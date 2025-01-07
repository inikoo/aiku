<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Retina\Storage\RecurringBill\Search;

use App\Actions\HydrateModel;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexRecurringBillSearch extends HydrateModel
{
    public string $commandSignature = 'search:retina_recurring_bills {organisations?*} {--s|slugs=}';


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

        $command->info("Reindex Recurring Bills");

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
