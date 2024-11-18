<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 18 Nov 2024 15:15:51 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Enums\Ordering\Purge\PurgeStateEnum;
use App\Enums\Ordering\Purge\PurgeTypeEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraPurge extends FetchAurora
{
    protected function parseModel(): void
    {

        $state = match ($this->auroraModelData->{'Order Basket Purge State'}) {
            'In Process' => PurgeStateEnum::IN_PROCESS,
            'Purging' => PurgeStateEnum::PURGING,
            'Finished' => PurgeStateEnum::FINISHED,
            default => PurgeStateEnum::CANCELLED
        };


        $this->parsedData['shop']  = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Order Basket Purge Store Key'});
        $this->parsedData['purge'] = [
            'state'           => $state,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Order Basket Purge Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
            'type'            => PurgeTypeEnum::MANUAL,
            'created_at'      => $this->parseDatetime($this->auroraModelData->{'Order Basket Purge Date'}),
            'inactive_days'   => $this->auroraModelData->{'Order Basket Purge Inactive Days'},
        ];


        if ($this->parseDatetime($this->auroraModelData->{'Order Basket Purge Start Purge Date'})) {
            $this->parsedData['purge']['start_at'] = $this->parseDatetime($this->auroraModelData->{'Order Basket Purge Start Purge Date'});
        }

        if ($this->parseDatetime($this->auroraModelData->{'Order Basket Purge Start Purge Date'})) {
            $this->parsedData['purge']['end_at'] = $this->parseDatetime($this->auroraModelData->{'Order Basket Purge End Purge Date'});
        }


        if ($this->auroraModelData->{'Order Basket Purge User Key'}) {
            $userId = $this->parseUser($this->organisation->id.':'.$this->auroraModelData->{'Order Basket Purge User Key'});
            if ($userId) {
                $this->parsedData['purge']['user_id'] = $userId->id;
            }
        }
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Order Basket Purge Dimension')
            ->where('Order Basket Purge Key', $id)->first();
    }
}
