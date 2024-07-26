<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 01:46:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Search;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceRecordSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Invoice $invoice): void
    {
        if ($invoice->trashed()) {
            if ($invoice->universalSearch) {
                $invoice->universalSearch()->delete();
            }

            return;
        }

        $shop = $invoice->shop;

        $universalSearchData = [
            'group_id'          => $invoice->group_id,
            'organisation_id'   => $invoice->organisation_id,
            'organisation_slug' => $invoice->organisation->slug,
            'shop_id'           => $shop->id,
            'shop_slug'         => $shop->slug,
            'customer_id'       => $invoice->customer_id,
            'customer_slug'     => $invoice->slug,
            'sections'          => ['accounting'],
            'haystack_tier_1'   => $invoice->number,
            'result'            => [
                'route'     => [
                    'name'          => 'grp.org.fulfilments.show.operations.invoices.show',
                    'parameters'    => [
                        $invoice->organisation->slug,
                        $invoice->shop->slug,
                        $invoice->slug
                        // 'organisation'=> $invoice->organisation->slug,
                        // 'fulfilment'  => $invoice->shop->fulfilment->slug,
                        // 'invoice'     => $invoice->slug
                    ]
                ],
                'container' => [
                    'label' => $invoice->shop->name,
                ],
                'title'     => $invoice->number,
                'icon'      => [
                    'icon' => 'fal fa-file-invoice-dollar',
                ],
                // 'aaa'       => $invoice,
                'meta'      => [
                    [
                        'key'   => 'type',
                        'label' => $invoice->type
                    ],
                    [
                        'key'   => 'created_date',
                        'type'  => 'date',
                        'label' => $invoice->created_at
                    ],
                    [
                        'key'    => 'total',
                        'type'   => 'amount',
                        'code'   => $invoice->currency->code,
                        'label'  => 'Total: ',
                        'amount' => $invoice->total_amount
                    ],
                ],
            ]
        ];

        if ($shop->type == ShopTypeEnum::FULFILMENT) {
            $universalSearchData['fulfilment_id']   = $shop->fulfilment->id;
            $universalSearchData['fulfilment_slug'] = $shop->fulfilment->slug;
        }


        $invoice->universalSearch()->updateOrCreate(
            [],
            $universalSearchData
        );


        $invoice->retinaSearch()->updateOrCreate(
            [],
            [
                'group_id'        => $invoice->group_id,
                'organisation_id' => $invoice->organisation_id,
                'customer_id'     => $invoice->customer_id,
                'haystack_tier_1' => $invoice->number,
            ]
        );
    }

}
