<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\Organisation;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateWarehouses
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {


        $stats = [
            'number_warehouses'                  => $organisation->warehouses()->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'warehouses',
                field: 'state',
                enum: WarehouseStateEnum::class,
                models: Warehouse::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->inventoryStats()->update($stats);
    }
}
