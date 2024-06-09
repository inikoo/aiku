<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Transfers\Aurora\FetchAurora;
use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraLocationStocks extends FetchAurora
{
    protected function parseModel(): void
    {



        $stockLocations = [];
        foreach ($this->auroraModelData as $modelData) {
            $location = $this->parseLocation($this->organisation->id.':'.$modelData->{'Location Key'});
            $settings = [];
            if ($modelData->{'Minimum Quantity'}) {
                $settings['min_stock'] = $modelData->{'Minimum Quantity'};
            }
            if ($modelData->{'Maximum Quantity'}) {
                $settings['max_stock'] = $modelData->{'Maximum Quantity'};
            }
            if ($modelData->{'Moving Quantity'}) {
                $settings['max_stock'] = $modelData->{'Moving Quantity'};
            }

            $pickingPriority = null;
            $type            = LocationStockTypeEnum::STORING->value;
            if ($modelData->{'Can Pick'}) {
                $type            = LocationStockTypeEnum::PICKING->value;
                $pickingPriority = is_null($pickingPriority) ? 0 : $pickingPriority + 1;
            }

            $stockLocations[$location->id] = [
                'quantity'           => round($modelData->{'Quantity On Hand'}, 3),
                'audited_at'         => $modelData->{'Part Location Last Audit'},
                'notes'              => $modelData->{'Part Location Note'},
                'data'               => [],
                'settings'           => $settings,
                'source_stock_id'    => $modelData->{'Part SKU'},
                'source_location_id' => $modelData->{'Location Key'},
                'picking_priority'   => $pickingPriority,
                'type'               => $type,
            ];
        }
        $this->parsedData['stock_locations'] = $stockLocations;
    }


    protected function fetchData($id): object|null
    {



        return DB::connection('aurora')
            ->table('Part Location Dimension')
            ->where('Part SKU', $id)->get();
    }
}
