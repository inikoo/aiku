<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Feb 2023 13:48:31 Malaysia Time, Ubud Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Invoice;

use App\Actions\WithActionUpdate;
use App\Models\Sales\Invoice;

class DeleteInvoice
{
    use WithActionUpdate;

    public function handle(
        Invoice $invoice,
        array $modelData
    ): Invoice {

        $invoice->invoiceTransactions()->delete();
        $invoice->delete();

        return $this->update($invoice, $modelData, ['data']);
    }
}
