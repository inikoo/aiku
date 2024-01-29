<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:19:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Fulfilment\Pallet\PalletStateEnum;
use App\Enums\Fulfilment\Pallet\PalletStatusEnum;
use App\Enums\Fulfilment\Pallet\PalletTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraPallet extends FetchAurora
{
    protected function parseModel(): void
    {

        $customer                     = $this->parseCustomer($this->organisation->id.':'.$this->auroraModelData->{'Fulfilment Asset Customer Key'});
        $this->parsedData['customer'] = $customer;

        $warehouse = $this->parseWarehouse($this->organisation->id.':'.$this->auroraModelData->{'Fulfilment Asset Warehouse Key'});


        $location_id = null;
        if ($this->auroraModelData->{'Fulfilment Asset Location Key'}) {
            $location = $this->parseLocation($this->organisation->id.':'.$this->auroraModelData->{'Fulfilment Asset Location Key'});
            if ($location) {
                $location_id = $location->id;
            }
        }

        $state  = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess' => PalletStateEnum::IN_PROCESS,
            'Received'  => PalletStateEnum::RECEIVED,
            'BookedIn'  => PalletStateEnum::BOOKED_IN,
            default     => PalletStateEnum::SETTLED
        };
        $status = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess', 'Received' => PalletStatusEnum::IN_PROCESS,
            'BookedIn' => PalletStatusEnum::STORING,
            'BookedOut', 'Invoiced' => PalletStatusEnum::RETURNED,
            'Lost' => PalletStatusEnum::LOST,
        };

        $type = match ($this->auroraModelData->{'Fulfilment Asset Type'}) {
            'Box'      => PalletTypeEnum::BOX,
            'Oversize' => PalletTypeEnum::OVERSIZE,
            default    => PalletTypeEnum::PALLET
        };


        $received_at = null;
        if ($this->auroraModelData->{'Fulfilment Asset To'}) {
            $received_at = $this->auroraModelData->{'Fulfilment Asset To'};
        }

        $reference = $this->auroraModelData->{'Fulfilment Asset Reference'};

        $reference = str_replace('&', 'and', $reference);
        $reference = str_replace(',', ' ', $reference);
        $reference = str_replace('\'', '', $reference);
        $reference = str_replace('"', '', $reference);
        if ($reference == '') {
            $reference = null;
        }


        $this->parsedData['pallet'] = [
            'warehouse_id'       => $warehouse->id,
            'state'              => $state,
            'status'             => $status,
            'type'               => $type,
            'customer_reference' => $reference,
            'notes'              => (string)$this->auroraModelData->{'Fulfilment Asset Note'},
            'created_at'         => $this->auroraModelData->{'Fulfilment Asset From'} ?? null,
            'received_at'        => $received_at,
            'source_id'          => $this->organisation->id.':'.$this->auroraModelData->{'Fulfilment Asset Key'},
        ];
        if ($location_id) {
            $this->parsedData['pallet']['location_id'] = $location_id;
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->where('Fulfilment Asset Key', $id)->first();
    }
}
