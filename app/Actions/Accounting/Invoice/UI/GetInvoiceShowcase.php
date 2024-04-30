<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:43:09 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Http\Resources\Helpers\AddressResource;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsObject;

class GetInvoiceShowcase
{
    use AsObject;

    public function handle(Invoice $invoice): array
    {
        return [
            'customer' => [
                'slug'         => $invoice->customer->slug,
                'reference'    => $invoice->customer->reference,
                'name'         => $invoice->customer->contact_name,
                'company_name' => $invoice->customer->company_name,
                'address'      => AddressResource::collection($invoice->customer->addresses),
            ],
            'number'           => $invoice->number,
            'type'             => $invoice->type,
            'currency'         => $invoice->currency->code,
            'paid_at'          => $invoice->paid_at,
            'group_exchange'   => $invoice->group_exchange,
            'org_exchange'     => $invoice->org_exchange,
            'net_amount'       => $invoice->net_amount,
            'total_amount'     => $invoice->total_amount,
            'payment_amount'   => $invoice->payment_amount,
            'group_net_amount' => $invoice->group_net_amount,
            'org_net_amount'   => $invoice->org_net_amount,
            'date'             => $invoice->date,
            'tax_liability_at' => $invoice->tax_liability_at,
            'transactions'     => $invoice->invoiceTransactions->map(function (InvoiceTransaction $transaction) {
                return [
                    'code'             => $transaction->item->code,
                    'name'             => $transaction->item->name,
                    'units'            => $transaction->item->units,
                    'quantity'         => $transaction->quantity,
                    'net_amount'       => $transaction->net_amount,
                    'discounts_amount' => $transaction->discounts_amount,
                    'tax_amount'       => $transaction->tax_amount,
                ];
            })
        ];
    }
}
