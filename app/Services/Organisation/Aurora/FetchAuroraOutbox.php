<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 10 Mar 2023 20:59:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FetchAuroraOutbox extends FetchAurora
{
    protected function parseModel(): void
    {
        $this->parsedData['code'] = $this->auroraModelData->{'Email Campaign Type Code'};

        $this->parsedData['shop']   = $this->parseShop($this->auroraModelData->{'Email Campaign Type Store Key'});
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
