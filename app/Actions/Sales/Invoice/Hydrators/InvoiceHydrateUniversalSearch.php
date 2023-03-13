<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 10 Mar 2023 11:05:41 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Sales\Invoice\Hydrators;

use App\Actions\WithTenantJob;
use App\Models\Sales\Invoice;
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
