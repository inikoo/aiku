<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Actions\Traits\WithTenantJob;
use App\Models\Accounting\Invoice;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceHydrateUniversalSearch
{
    use AsAction;
    use WithTenantJob;

    public function handle(Invoice $invoice): void
    {
        $invoice->universalSearch()->create(
            [
                'section' => 'Accounting',
                'route'   => json_encode([
                    'name'      => 'accounting.invoices.show',
                    'arguments' => [
                        $invoice->slug
                    ]
                ]),
                'icon'           => 'fa-file-invoice-dollar',
                'primary_term'   => $invoice->number.' '.$invoice->order_id,
                'secondary_term' => $invoice->shop_id.' '.$invoice->customer_id
            ]
        );
    }

}
