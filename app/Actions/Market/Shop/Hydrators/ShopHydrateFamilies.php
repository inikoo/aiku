<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 31 Mar 2024 15:32 Malaysia Time, Plane KL - Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Market\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Market\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Market\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Market\ProductCategory;
use App\Models\Market\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateFamilies
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
            'number_families' => $shop->families()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'families',
                field: 'state',
                enum: ProductCategoryStateEnum::class,
                models: ProductCategory::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id)->where('type', ProductCategoryTypeEnum::FAMILY);
                }
            )
        );

        $shop->stats()->update($stats);
    }


}
