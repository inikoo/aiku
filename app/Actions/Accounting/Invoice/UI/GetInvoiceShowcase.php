<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:43:09 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Http\Resources\CRM\CustomerResource;
use App\Http\Resources\Sales\OrderResource;
use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsObject;

class GetInvoiceShowcase
{
    use AsObject;

    public function handle(Invoice $invoice): array
    {
        return [
            'customer'     => CustomerResource::make($invoice->customer),
            'transactions' => $invoice->invoiceTransactions,
            'order'        => OrderResource::make($invoice->order)
        ];
    }
}
