<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Accounting\Refund;

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
        });
    }

    public function action(Invoice $invoice, array $modelData): void
    {
        $this->initialisationFromShop($invoice->shop, $modelData);

        $this->handle($invoice, $this->validatedData);
    }
}
