<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceHydrateUniversalSearch
{
    use AsAction;

    public string $jobQueue = 'universal-search';

    public function handle(Invoice $invoice): void
    {
        if($invoice->trashed()) {
            return;
        }

        $shop=$invoice->shop;

        $modelData=[
            'group_id'          => $invoice->group_id,
            'organisation_id'   => $invoice->organisation_id,
            'organisation_slug' => $invoice->organisation->slug,
            'shop_id'           => $shop->id,
            'shop_slug'         => $shop->slug,
            'customer_id'       => $invoice->customer_id,
            'customer_slug'     => $invoice->customer->slug,
            'section'           => 'accounting',
            'title'             => $invoice->number,
        ];

        if($shop->type==ShopTypeEnum::FULFILMENT) {
            $modelData['fulfilment_id']     = $shop->fulfilment->id;
            $modelData['fulfilment_slug']   = $shop->fulfilment->slug;
        }


        $invoice->universalSearch()->updateOrCreate(
            [],
            $modelData
        );
    }

}
