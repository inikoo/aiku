<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 25 Jul 2024 20:21:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\PalletReturn\PalletReturnStateEnum;
use App\Models\Fulfilment\PalletReturn;
use App\Models\Inventory\Warehouse;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class WarehouseHydratePalletReturns
{
    use AsAction;
    use WithEnumStats;

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
        $stats = [
            'number_pallet_returns' => PalletReturn::where('warehouse_id', $warehouse->id)->count()
        ];

        $stats = array_merge($stats, $this->getEnumStats(
            model: 'pallet_returns',
            field: 'state',
            enum: PalletReturnStateEnum::class,
            models: PalletReturn::class,
            where: function ($q) use ($warehouse) {
                $q->where('warehouse_id', $warehouse->id);
            }
        ));


        $warehouse->stats()->update($stats);
    }
}
