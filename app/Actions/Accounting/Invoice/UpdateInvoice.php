<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Accounting\InvoiceResource;
use App\Models\Accounting\Invoice;
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

    public function rules(): array
    {
        return [
            'number'      => ['sometimes', 'unique:tenant.invoices', 'numeric'],
            'currency_id' => ['sometimes', 'required', 'exists:central.currencies,id']
        ];
    }

    public function action(Invoice $invoice, array $modelData): Invoice
    {
        return $this->handle($invoice, $modelData);
    }

    public function jsonResponse(Invoice $invoice): InvoiceResource
    {
        return new InvoiceResource($invoice);
    }
}
