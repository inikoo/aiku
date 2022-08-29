<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:12:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Actions\SourceUpserts\Aurora\Single\UpsertOrderFromSource;
use App\Models\Helpers\Address;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class FetchAuroraDeliveryNote extends FetchAurora
{

    protected function parseModel(): void
    {

        $this->parsedData['order'] = UpsertOrderFromSource::run($this->organisationSource,$this->auroraModelData->{'Delivery Note Order Key'});

        $this->parsedData['delivery_note'] = [
            'number'                 => $this->auroraModelData->{'Delivery Note ID'},
            'state'                  => Str::snake($this->auroraModelData->{'Delivery Note State'}, '-'),
            'type'                   => match ($this->auroraModelData->{'Delivery Note Type'}) {
                'Replacement & Shortages', 'Replacement', 'Shortages' => 'replacement',
                default => 'order'
            },
            'date'                   => $this->auroraModelData->{'Delivery Note Date'},
            'picking_at'             => $this->auroraModelData->{'Delivery Note Date Start Picking'},
            'picked_at'              => $this->auroraModelData->{'Delivery Note Date Finish Picking'},
            'packing_at'             => $this->auroraModelData->{'Delivery Note Date Start Packing'},
            'packed_at'              => $this->auroraModelData->{'Delivery Note Date Finish Packing'},
            'created_at'             => $this->auroraModelData->{'Delivery Note Date Created'},
            'organisation_source_id' => $this->auroraModelData->{'Delivery Note Key'},

        ];

        $deliveryAddressData                 = $this->parseAddress(prefix: 'Delivery Note', auAddressData: $this->auroraModelData);
        $this->parsedData['delivery_address'] = new Address($deliveryAddressData);
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Delivery Note Dimension')
            ->where('Delivery Note Key', $id)->first();
    }

}
