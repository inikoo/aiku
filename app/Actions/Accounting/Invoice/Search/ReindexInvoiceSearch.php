<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:46:31 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Search;

use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use Illuminate\Support\Collection;

class ReindexInvoiceSearch extends HydrateModel
{
    public string $commandSignature = 'invoice:search {organisations?*} {--s|slugs=}';


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
}
