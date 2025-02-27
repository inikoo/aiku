<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-08h-24m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoice;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithOrderExchanges;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\FulfilmentCustomer;
use Lorisleiva\Actions\ActionRequest;

class StoreStandaloneFulfilmentInvoice extends OrgAction
{
    use WithFixedAddressActions;
    use WithOrderExchanges;
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(FulfilmentCustomer $parent): Invoice
    {
        $invoiceData = [
            'currency_id'     => $this->fulfilment->shop->currency_id,
            'type'            => InvoiceTypeEnum::INVOICE,
            'net_amount'      => 0,
            'total_amount'    => 0,
            'gross_amount'    => 0,
            'tax_amount'      => 0,
            'in_process'      => true,
        ];

        $invoice = StoreInvoice::make()->action($parent->customer, $invoiceData);

        return $invoice;
    }

    public function rules(): array
    {
        return [
            'tax_category_id' => ['sometimes', 'exists:tax_categories,id'],
        ];
    }
    /**
     * @throws \Throwable
     */
    public function asController(FulfilmentCustomer $parent, ActionRequest $request): Invoice
    {
        $this->initialisationFromFulfilment($parent->fulfilment, $request);

        return $this->handle($parent, $this->validatedData);
    }

}
