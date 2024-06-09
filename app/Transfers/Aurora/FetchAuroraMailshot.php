<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;
use Str;

class FetchAuroraMailshot extends FetchAurora
{
    protected function parseModel(): void
    {
        $state = match ($this->auroraModelData->{'Email Campaign State'}) {
            default=> Str::kebab($this->auroraModelData->{'Email Campaign State'})
        };

        $this->parsedData['outbox']   = $this->parseOutbox($this->auroraModelData->{'Email Campaign Email Template Type Key'});
        $this->parsedData['mailshot'] = [
            'state'      => $state,
            'source_id'  => $this->organisation->id.':'.$this->auroraModelData->{'Email Campaign Key'},
            'created_at' => $this->parseDate($this->auroraModelData->{'Email Campaign Creation Date'})
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Email Campaign Dimension')
            ->where('Email Campaign Key', $id)->first();
    }
}
