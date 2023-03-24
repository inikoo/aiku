<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Accounting\Invoice;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class InvoiceHydrateUniversalSearch implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;

    public function handle(Invoice $invoice): void
    {
        $invoice->universalSearch()->create(
            [
                'primary_term'   => $invoice->number.' '.$invoice->order_id,
                'secondary_term' => $invoice->shop_id.' '.$invoice->customer_id
            ]
        );
    }

    public function getJobUniqueId(Invoice $invoice): int
    {
        return $invoice->id;
    }
}
