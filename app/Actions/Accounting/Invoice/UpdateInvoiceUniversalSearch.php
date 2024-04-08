<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 06 Apr 2024 17:53:45 Central Indonesia Time, Sanur , Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\HydrateModel;
use App\Models\Accounting\Invoice;
use Illuminate\Support\Collection;

class UpdateInvoiceUniversalSearch extends HydrateModel
{
    public string $commandSignature = 'invoice:search {organisations?*} {--s|slugs=}';


    public function handle(Invoice $invoice): void
    {
        InvoiceHydrateUniversalSearch::run($invoice);
    }


    protected function getModel(string $slug): Invoice
    {
        return Invoice::where('slug', $slug)->first();
    }

    protected function getAllModels(): Collection
    {
        return Invoice::get();
    }
}
