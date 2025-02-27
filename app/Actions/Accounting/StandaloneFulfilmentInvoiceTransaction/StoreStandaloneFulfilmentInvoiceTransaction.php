<?php

/*
 * author Arya Permana - Kirin
 * created on 27-02-2025-08h-39m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\StandaloneFulfilmentInvoiceTransaction;

use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransaction;
use App\Actions\Accounting\StandaloneFulfilmentInvoice\CalculateStandaloneFulfilmentInvoiceTotals;
use App\Actions\OrgAction;
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Actions\Traits\WithOrderExchanges;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Catalogue\HistoricAsset;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class StoreStandaloneFulfilmentInvoiceTransaction extends OrgAction
{
    use WithOrderExchanges;
    use WithNoStrictRules;

    public function handle(Invoice $invoice, HistoricAsset $historicAsset, array $modelData): InvoiceTransaction
    {
        $amount = $historicAsset->price * Arr::get($modelData, 'quantity');
        data_set($modelData, 'tax_category_id', $invoice->tax_category_id);
        data_set($modelData, 'gross_amount', $amount);
        data_set($modelData, 'net_amount', $amount);
        data_set($modelData, 'in_process', true);
        $invoiceTransaction = StoreInvoiceTransaction::make()->action($invoice, $historicAsset, $modelData);

        CalculateStandaloneFulfilmentInvoiceTotals::run($invoice);

        return $invoiceTransaction;
    }

    public function rules(): array
    {
        $rules = [
            'quantity'        => ['required', 'numeric'],
        ];

        return $rules;
    }


    public function asController(Invoice $invoice, HistoricAsset $historicAsset, ActionRequest $request): InvoiceTransaction
    {
        $this->initialisationFromShop($invoice->shop, $request);

        return $this->handle($invoice, $historicAsset, $this->validatedData);
    }


}
