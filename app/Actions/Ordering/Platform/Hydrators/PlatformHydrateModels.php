<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:53:53 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Platforming\Platform\Hydrators;

use App\Actions\Traits\WithEnumStats;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\CRM\Customer\CustomerStateEnum;
use App\Enums\Ordering\Order\OrderHandingTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Product;
use App\Models\CRM\Customer;
use App\Models\Ordering\Order;
use App\Models\Ordering\Platform;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class PlatformHydrateModels
{
    use AsAction;
    use WithEnumStats;
    private Platform $platform;

    public function __construct(Platform $platform)
    {
        $this->platform = $platform;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->platform->id))->dontRelease()];
    }
    public function handle(Platform $platform): void
    {
        $stats= [
            'number_customers' => $platform->customers()->count(),
            'number_orders'    => $platform->orders()->count(),
            'number_products'  => $platform->products()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'products',
                field: 'state',
                enum: ProductStateEnum::class,
                models: Product::class
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'customers',
                field: 'state',
                enum: CustomerStateEnum::class,
                models: Customer::class
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'state',
                enum: OrderStateEnum::class,
                models: Order::class
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'orders',
                field: 'handing_type',
                enum: OrderHandingTypeEnum::class,
                models: Order::class
            )
        );

        $platform->stats()->update($stats);
    }
}
