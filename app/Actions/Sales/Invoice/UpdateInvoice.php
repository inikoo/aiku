<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Feb 2023 13:13:31 Malaysia Time, Ubud, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Invoice;

use App\Actions\Sales\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Models\Sales\Invoice;
use Illuminate\Support\Arr;

class UpdateInvoice
{
    use WithActionUpdate;

    public function handle(Invoice $invoice, array $modelData): Invoice
    {
        $invoice->update(Arr::except($modelData, ['data']));
        $invoice->update($this->extractJson($modelData));

        InvoiceHydrateUniversalSearch::dispatch($invoice);

        return $this->update($invoice, $modelData, ['data']);
    }
}
