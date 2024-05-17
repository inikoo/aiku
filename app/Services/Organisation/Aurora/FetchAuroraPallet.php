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
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraPallet extends FetchAurora
{
    protected function parseModel(): void
    {
        $customer = $this->parseCustomer(
            $this->organisation->id . ':' . $this->auroraModelData->{'Fulfilment Asset Customer Key'}
        );

        $shop = $customer->shop;


        if (!$customer) {
            dd("Error Customer not found");
        }

        if ($shop->type != ShopTypeEnum::FULFILMENT) {
            dd("Error Shop not fulfilment");
        }

        if (!$customer->is_fulfilment) {
            dd('error customer not fulfilment');
        }


        $this->parsedData['customer'] = $customer;

        $warehouse = $this->parseWarehouse(
            $this->organisation->id . ':' . $this->auroraModelData->{'Fulfilment Asset Warehouse Key'}
        );


        $location_id = null;
        if ($this->auroraModelData->{'Fulfilment Asset Location Key'}) {
            $location = $this->parseLocation(
                $this->organisation->id . ':' . $this->auroraModelData->{'Fulfilment Asset Location Key'}
            );
            $location_id = $location?->id;
        }

        $state = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess' => PalletStateEnum::IN_PROCESS,
            'Received'  => PalletStateEnum::RECEIVED,
            'BookedIn'  => PalletStateEnum::STORING,
            default     => PalletStateEnum::DISPATCHED
        };
        $status = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess' => PalletStatusEnum::IN_PROCESS,
            'Received'  => PalletStatusEnum::RECEIVING,
            'BookedIn'  => PalletStatusEnum::STORING,
            'BookedOut', 'Invoiced' => PalletStatusEnum::RETURNED,
            'Lost' => PalletStatusEnum::INCIDENT,
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

        $notes = (string)$this->auroraModelData->{'Fulfilment Asset Note'};
        $notes = strip_tags($notes);
        $notes = str_replace('&nbsp;', ' ', $notes);
        $notes = trim($notes);

        $this->parsedData['pallet'] = [
            'warehouse_id'       => $warehouse->id,
            'state'              => $state,
            'status'             => $status,
            'type'               => $type,
            'customer_reference' => $reference,
            'notes'              => $notes,
            'created_at'         => $this->auroraModelData->{'Fulfilment Asset From'} ?? null,
            'received_at'        => $received_at,
            'source_id'          => $this->organisation->id . ':' . $this->auroraModelData->{'Fulfilment Asset Key'},
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
