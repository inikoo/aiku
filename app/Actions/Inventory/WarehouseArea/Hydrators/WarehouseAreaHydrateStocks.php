<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:49:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea\Hydrators;

use App\Models\Inventory\WarehouseArea;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseAreaHydrateStocks implements ShouldBeUnique
{
    use AsAction;

    private WarehouseArea $warehouseArea;

    public function __construct(WarehouseArea $warehouseArea)
    {
        $this->warehouseArea = $warehouseArea;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->warehouseArea->id))->dontRelease()];
    }

    public function handle(WarehouseArea $warehouseArea): void
    {

        $warehouseArea->stats()->update(
            [
                'stock_value'            => $warehouseArea->locations()->sum('stock_value'),
                'stock_commercial_value' => $warehouseArea->locations()->sum('stock_commercial_value'),
            ]
        );
    }


}
