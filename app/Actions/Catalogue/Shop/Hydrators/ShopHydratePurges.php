<?php

/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-10h-02m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use App\Models\Catalogue\Shop;
use App\Models\Ordering\Purge;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydratePurges
{
    use AsAction;
    use WithEnumStats;

    private Shop $shop;

    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->shop->id))->dontRelease()];
    }

    public function handle(Shop $shop): void
    {
        $stats = [
            'number_purges' => $shop->purges()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purges',
                field: 'state',
                enum: PurgeStateEnum::class,
                models: Purge::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'purges',
                field: 'type',
                enum: PurgeTypeEnum::class,
                models: Purge::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $shop->orderingStats()->update($stats);
    }

}
