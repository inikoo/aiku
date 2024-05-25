<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:12:14 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\Utils\Abbreviate;
use App\Enums\Inventory\Warehouse\WarehouseStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraWarehouse extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData["warehouse"] = [
            "code"       => Abbreviate::run($this->auroraModelData->{'Warehouse Name'}),
            "name"       => $this->auroraModelData->{'Warehouse Name'},
            'state'      => match ($this->auroraModelData->{'Warehouse State'}) {
                'Active' => WarehouseStateEnum::OPEN->value,
                default  => WarehouseStateEnum::CLOSED->value
            },
            'settings'   => [
                'address_link' => 'Organisation:default'
            ],
            "source_id"  => $this->organisation->id.':'.$this->auroraModelData->{'Warehouse Key'},
            "created_at" =>
                $this->auroraModelData->{'Warehouse Valid From'} ?? null,
        ];
    }

    protected function fetchData($id): object|null
    {
        return DB::connection("aurora")
            ->table("Warehouse Dimension")
            ->where("Warehouse Key", $id)
            ->first();
    }
}
