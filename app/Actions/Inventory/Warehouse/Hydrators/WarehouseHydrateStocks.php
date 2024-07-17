<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:41:29 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Models\Inventory\Warehouse;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydrateStocks
{
    use AsAction;

    private Warehouse $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->warehouse->id))->dontRelease()];
    }


    public function handle(Warehouse $warehouse): void
    {

        $warehouse->stats()->update(
            [
                'stock_value'            => $warehouse->locations()->sum('stock_value'),
                'stock_commercial_value' => $warehouse->locations()->sum('stock_commercial_value'),
            ]
        );
    }

}
