<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraOutbox extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['code'] = $this->auroraModelData->{'Email Campaign Type Code'};

        $this->parsedData['shop']   = $this->parseShop($this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Type Store Key'});
        $this->parsedData['outbox'] = [
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Type Key'},
            'state'                    => Str::kebab($this->auroraModelData->{'Email Campaign Type Status'})
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Campaign Type Dimension')
            ->where('Email Campaign Type Key', $id)->first();
    }
}
