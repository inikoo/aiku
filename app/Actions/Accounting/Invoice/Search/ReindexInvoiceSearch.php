<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:46:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ReindexInvoiceSearch extends HydrateModel
{
    public string $commandSignature = 'search:invoices {organisations?*} {--s|slugs=}';


    public function handle(Invoice $invoice): void
    {
        InvoiceRecordSearch::run($invoice);
    }


    protected function getModel(string $slug): Invoice
    {
        return Invoice::withTrashed()->where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Invoice::withTrashed()->get();
    }

    protected function loopAll(Command $command): void
    {
        $command->info("Reindex Invoices");
        $count = Invoice::withTrashed()->count();

        $bar = $command->getOutput()->createProgressBar($count);
        $bar->setFormat('debug');
        $bar->start();

        Invoice::withTrashed()->chunk(1000, function (Collection $models) use ($bar) {
            foreach ($models as $model) {
                $this->handle($model);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->info("");
    }
}
