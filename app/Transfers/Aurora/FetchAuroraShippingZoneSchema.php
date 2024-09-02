<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 02 Sept 2024 12:27:54 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Ordering\ShippingZoneSchema\ShippingZoneSchemaTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraShippingZoneSchema extends FetchAurora
{
    protected function parseModel(): void
    {
        $shop = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Shipping Zone Schema Store Key'});


        $type = match ($this->auroraModelData->{'Shipping Zone Schema Type'}) {
            'Current'   => ShippingZoneSchemaTypeEnum::CURRENT,
            'InReserve' => ShippingZoneSchemaTypeEnum::IN_RESERVE,
            'Deal'      => ShippingZoneSchemaTypeEnum::DEAL,
            default     => null
        };

        $this->parsedData['shop']                 = $shop;
        $this->parsedData['shipping-zone-schema'] = [
            'type'            => $type,
            'name'            => $this->auroraModelData->{'Shipping Zone Schema Label'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Shipping Zone Schema Key'},

        ];


        $createdBy = $this->auroraModelData->{'Shipping Zone Schema Creation Date'};

        if ($createdBy) {
            $this->parsedData['shipping-zone-schema']['created_by'] = $createdBy;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Shipping Zone Schema Dimension')
            ->where('Shipping Zone Schema Key', $id)->first();
    }
}
