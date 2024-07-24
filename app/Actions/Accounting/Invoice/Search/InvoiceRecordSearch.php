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
            'customer_slug'     => $invoice->customer->slug,
            'sections'          => ['accounting'],
            'haystack_tier_1'   => $invoice->number,
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
                'group_id'          => $invoice->group_id,
                'organisation_id'   => $invoice->organisation_id,
                'customer_id'       => $invoice->customer_id,
                'haystack_tier_1'   => $invoice->number,
            ]
        );

    }

}
