<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Apr 2024 15:33:01 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Billables\Rental\RentalStateEnum;
use App\Models\Billables\Rental;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateRentals
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

        $stats         = [];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'rentals',
                field: 'state',
                enum: RentalStateEnum::class,
                models: Rental::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );

        $shop->stats()->update($stats);
    }

}
