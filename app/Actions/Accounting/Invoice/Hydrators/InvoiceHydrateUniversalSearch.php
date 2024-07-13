<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Hydrators;

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

        $invoice->universalSearch()->updateOrCreate(
            [],
            [
                'group_id'          => $invoice->group_id,
                'organisation_id'   => $invoice->organisation_id,
                'organisation_slug' => $invoice->organisation->slug,
                'shop_id'           => $invoice->shop_id,
                'shop_slug'         => $invoice->shop->slug,
                'customer_id'       => $invoice->customer_id,
                'customer_slug'     => $invoice->customer->slug,
                'section'           => 'accounting',
                'title'             => $invoice->number,
            ]
        );
    }

}
