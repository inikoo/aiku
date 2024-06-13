<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 04:18:11 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Shop\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Shop;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ShopHydrateCustomerClients
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
            'number_customer_clients'                            => $shop->clients()->count(),
            'number_current_customer_clients'                    => $shop->clients()->where('status', true)->count()
        ];

        $shop->dropshippingStats()->update($stats);
    }
}
