<?php

/*
 * author Arya Permana - Kirin
 * created on 19-12-2024-09h-49m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateIntervals;
use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateInvoicedCustomers
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateIntervals;
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
        $dateRanges = $this->getDateRanges();
        $stats = [];

        foreach ($dateRanges as $key => $range) {
            if ($key === 'all') {
                $invoices = $asset->invoiceTransactions()
                    ->with('invoice')
                    ->get()
                    ->pluck('invoice')
                    ->filter()
                    ->unique('customer_id');
            } else {
                [$start, $end] = $range;

                $invoices = $asset->invoiceTransactions()
                    ->with('invoice')
                    ->whereHas('invoice', function ($query) use ($start, $end) {
                        $query->whereBetween('created_at', [$start, $end]);
                    })
                    ->get()
                    ->pluck('invoice')
                    ->filter()
                    ->unique('customer_id');
            }

            $stats["customers_invoiced_{$key}"] = $invoices->count();
        }

        $asset->orderingIntervals()->update($stats);

    }

}
