<?php

/*
 * author Arya Permana - Kirin
 * created on 19-12-2024-10h-57m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateIntervals;
use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateOrders
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

        // TODO: #1446 refactor remove the with
        foreach ($dateRanges as $key => $range) {
            if ($key === 'all') {
                $orders = $asset->transactions()
                    ->with('order')
                    ->get()
                    ->pluck('order')
                    ->filter()
                    ->unique('id');
            } else {
                [$start, $end] = $range;

                $orders = $asset->transactions()
                    ->with('order')
                    ->whereHas('order', function ($query) use ($start, $end) {
                        $query->whereBetween('created_at', [$start, $end]);
                    })
                    ->get()
                    ->pluck('order')
                    ->filter()
                    ->unique('id');
            }

            $stats["orders_{$key}"] = $orders->count();
        }


        $asset->orderingIntervals()->update($stats);

    }

}
