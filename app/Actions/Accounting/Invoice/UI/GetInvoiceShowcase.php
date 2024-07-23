<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:43:09 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\UI;

use App\Actions\Accounting\InvoiceTransaction\UI\IndexInvoiceTransactions;
use App\Enums\UI\Accounting\InvoiceTabsEnum;
use App\Http\Resources\Accounting\InvoiceTransactionsResource;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use Lorisleiva\Actions\Concerns\AsObject;

class GetInvoiceShowcase
{
    use AsObject;

    public function handle(Invoice $invoice): array
    {
        return [
            'items'    => InvoiceTransactionsResource::collection(IndexInvoiceTransactions::run($invoice, InvoiceTabsEnum::ITEMS->value)),
            'customer' => [
                'slug'         => $invoice->customer->slug,
                'reference'    => $invoice->customer->reference,
                'contact_name' => $invoice->customer->contact_name,
                'company_name' => $invoice->customer->company_name,
                'location'     => $invoice->customer->location,
                'phone'        => $invoice->customer->phone,
                // 'address'      => AddressResource::collection($invoice->customer->addresses),
            ],
            // 'invoice_information'   => [
            //     'number'                    => $invoice->number,
            //     'profit_amount'             => $invoice->profit_amount,
            //     'margin_percentage'         => $invoice->margin_percentage,
            //     'date'                      => $invoice->date,

            //     'item_gross'                => $invoice->item_gross,
            //     'discounts_total'           => $invoice->discounts_total,
            //     'items_net'                 => $invoice->items_net,
            //     'charges'                   => $invoice->charges,
            //     'shipping'                  => $invoice->shipping,
            //     'insurance'                 => $invoice->insurance,

            //     'net_amount'       => $invoice->net_amount,
            //     'tax_amount'       => $invoice->tax_amount,
            //     'tax_percentage'   => $invoice->tax_percentage,
            //     'payment_amount'   => $invoice->payment_amount,

            //     'total_amount'     => $invoice->total_amount,
            // ],
            'currency'         => $invoice->currency->code,
            'type'             => $invoice->type,
            'paid_at'          => $invoice->paid_at,
            'grp_exchange'     => $invoice->grp_exchange,
            'org_exchange'     => $invoice->org_exchange,
            'grp_net_amount'   => $invoice->grp_net_amount,
            'org_net_amount'   => $invoice->org_net_amount,
            'tax_liability_at' => $invoice->tax_liability_at,

            'transactions'     => $invoice->invoiceTransactions->map(function (InvoiceTransaction $transaction) {
                return [
                    'code'             => $transaction->asset->code,
                    'name'             => $transaction->asset->name,
                    'units'            => $transaction->asset->units,
                    'quantity'         => $transaction->quantity,
                    'net_amount'       => $transaction->net_amount,
                    'discounts_amount' => $transaction->discounts_amount,
                    'tax_amount'       => $transaction->tax_amount,
                ];
            }),
            'exportPdfRoute' => [
                'name'       => 'grp.org.accounting.invoices.download',
                'parameters' => [
                    'organisation' => $invoice->organisation->slug,
                    'invoice'      => $invoice->slug
                ]
            ]
        ];
    }
}
