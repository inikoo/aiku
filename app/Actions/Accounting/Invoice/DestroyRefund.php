<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Feb 2025 12:51:18 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\CRM\Customer\Hydrators\CustomerHydrateInvoices;
use App\Actions\OrgAction;
use App\Models\Accounting\Invoice;
use Illuminate\Support\Facades\DB;

class DestroyRefund extends OrgAction
{
    public function handle(Invoice $invoice, array $modelData): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->invoiceTransactions()->forceDelete();
            $invoice->forceDelete();

            if ($invoice->customer_id) {
                CustomerHydrateInvoices::dispatch($invoice->customer);
            }
        });
    }

    public function action(Invoice $invoice, array $modelData): void
    {
        $this->initialisationFromShop($invoice->shop, $modelData);

        $this->handle($invoice, $this->validatedData);
    }
}
