<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Accounting\Invoice\Hydrators\InvoiceHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithFixedAddressActions;
use App\Http\Resources\Accounting\InvoicesResource;
use App\Models\Accounting\Invoice;
use App\Rules\IUnique;
use App\Rules\ValidAddress;

class UpdateInvoice extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;


    private Invoice $invoice;

    public function handle(Invoice $invoice, array $modelData): Invoice
    {
        $billingAddressData = $modelData['billing_address'];
        data_forget($modelData, 'billing_address');

        $invoice = $this->update($invoice, $modelData, ['data']);

        if ($billingAddressData) {
            $invoice = $this->updateFixedAddress(
                $invoice,
                $invoice->billingAddress,
                $billingAddressData,
                'Ordering',
                'billing',
                'address_id'
            );
        }

        InvoiceHydrateUniversalSearch::dispatch($invoice);

        return $invoice;
    }

    public function rules(): array
    {
        return [
            'number'           => [
                'sometimes',
                'string',
                'max:64',
                new IUnique(
                    table: 'invoices',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'id', 'value' => $this->invoice->id, 'operator' => '!=']
                    ]
                ),
            ],
            'currency_id'      => ['sometimes', 'required', 'exists:currencies,id'],
            'net_amount'       => ['sometimes', 'required', 'numeric'],
            'total_amount'     => ['sometimes', 'required', 'numeric'],
            'date'             => ['sometimes', 'date'],
            'tax_liability_at' => ['sometimes', 'date'],
            'billing_address'  => ['sometimes', 'required', new ValidAddress()],

        ];
    }

    public function action(Invoice $invoice, array $modelData, int $hydratorsDelay = 0): Invoice
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->invoice        = $invoice;

        $this->initialisationFromShop($invoice->shop, $modelData);

        return $this->handle($invoice, $this->validatedData);
    }

    public function jsonResponse(Invoice $invoice): InvoicesResource
    {
        return new InvoicesResource($invoice);
    }
}
