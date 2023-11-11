<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:19:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Enums\Fulfilment\StoredItem\StoredItemStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraStoredItem extends FetchAurora
{
    protected function parseModel(): void
    {
        $customer                     = $this->parseCustomer($this->auroraModelData->{'Fulfilment Asset Customer Key'});
        $this->parsedData['customer'] = $customer;

        $state  = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess' => StoredItemStateEnum::IN_PROCESS,
            'Received'  => StoredItemStateEnum::RECEIVED,
            'BookedIn'  => StoredItemStateEnum::BOOKED_IN,
            default     => StoredItemStateEnum::SETTLED
        };
        $status = match ($this->auroraModelData->{'Fulfilment Asset State'}) {
            'InProcess', 'Received' => StoredItemStatusEnum::IN_PROCESS,
            'BookedIn' => StoredItemStatusEnum::STORING,
            'Invoiced' => StoredItemStatusEnum::RETURNED,
            'Lost'     => StoredItemStatusEnum::LOST,
        };

        $type = Str::snake(strtolower($this->auroraModelData->{'Fulfilment Asset Type'}), '-');

        $received_at = null;
        if ($this->auroraModelData->{'Fulfilment Asset To'}) {
            $received_at = $this->auroraModelData->{'Fulfilment Asset To'};
        }

        $this->parsedData['storedItem'] = [
            'state'       => $state,
            'status'      => $status,
            'type'        => $type,
            'reference'   => $this->auroraModelData->{'Fulfilment Asset Reference'}??$this->auroraModelData->{'Fulfilment Asset Key'},
            'notes'       => $this->auroraModelData->{'Fulfilment Asset Note'},
            'created_at'  => $this->auroraModelData->{'Fulfilment Asset From'} ?? null,
            'received_at' => $received_at,
            'source_id'   => $this->auroraModelData->{'Fulfilment Asset Key'},
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->where('Fulfilment Asset Key', $id)->first();
    }
}
