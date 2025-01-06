<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateDeliveryNotes;
use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateInvoicesStats
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateDeliveryNotes;

    private Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->asset->id))->dontRelease()];
    }

    public function handle(Asset $asset): void
    {
        $invoices = $asset->invoiceTransactions()
                    ->with('invoice')
                    ->get()
                    ->pluck('invoice')
                    ->filter()
                    ->unique('customer_id');

        $stats          = [
            'number_invoiced_customers'              => $invoices->count(),
        ];


        $asset->orderingStats()->update($stats);
    }

}
