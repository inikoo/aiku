<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 19 Oct 2022 19:51:56 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Sales\InvoiceTransaction\StoreInvoiceTransaction;
use App\Models\Sales\Invoice;
use App\Models\Sales\InvoiceTransaction;
use App\Services\Tenant\SourceTenantService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class FetchInvoiceTransactions
{
    use AsAction;


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $source_id, Invoice $invoice): ?InvoiceTransaction
    {
        if ($transactionData = $tenantSource->fetchInvoiceTransaction(id: $source_id)) {
            if (!InvoiceTransaction::where('source_id', $transactionData['transaction']['source_id'])
                ->first()) {
                return StoreInvoiceTransaction::run(
                    invoice:   $invoice,
                    modelData: $transactionData['transaction']
                );
            }
        }


        return null;
    }


}
