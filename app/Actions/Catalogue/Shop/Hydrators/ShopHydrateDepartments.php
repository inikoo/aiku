<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:57:22 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Catalogue\ProductCategory\ProductCategoryStateEnum;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateDepartments
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
            'number_departments' => $shop->departments()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'departments',
                field: 'state',
                enum: ProductCategoryStateEnum::class,
                models: ProductCategory::class,
                where: function ($q) use ($shop) {
                    $q->where('shop_id', $shop->id)->where('type', ProductCategoryTypeEnum::DEPARTMENT);
                }
            )
        );

        $shop->stats()->update($stats);
    }


}
