<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:51:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransaction;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Transfers\SourceOrganisationService;
use Lorisleiva\Actions\Concerns\AsAction;

class FetchInvoiceTransactions
{
    use AsAction;


    public function handle(SourceOrganisationService $organisationSource, int $source_id, Invoice $invoice): ?InvoiceTransaction
    {
        if ($transactionData = $organisationSource->fetchInvoiceTransaction(
            id: $source_id,
            invoice: $invoice,
            isFulfilment: $invoice->shop->type === ShopTypeEnum::FULFILMENT
        )) {
            if (!InvoiceTransaction::where('source_id', $transactionData['transaction']['source_id'])
                ->first()) {
                return StoreInvoiceTransaction::make()->action(
                    invoice: $invoice,
                    model: $transactionData['model'],
                    modelData: $transactionData['transaction'],
                    strict: false
                );
            }
        }


        return null;
    }
}
