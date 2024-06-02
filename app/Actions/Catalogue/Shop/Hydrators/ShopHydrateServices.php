<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 10:16:01 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Models\Catalogue\Service;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateServices
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
                model: 'services',
                field: 'state',
                enum: ServiceStateEnum::class,
                models: Service::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id);
                }
            )
        );



        $shop->stats()->update($stats);
    }

}
