<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 17 Nov 2024 15:36:21 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraPollHasCustomer extends FetchAurora
{
    protected function parseModel(): void
    {
        $pollOption = null;
        if ($this->auroraModelData->{'Customer Poll Query Option Key'}) {
            $pollOption = $this->parsePollOption($this->organisation->id.':'.$this->auroraModelData->{'Customer Poll Query Option Key'});
            if (!$pollOption) {

                print "WTF option not found (".$this->auroraModelData->{'Customer Poll Query Option Key'}.") \n";
                return;
            }


        }


        $this->parsedData['poll'] = $this->parsePoll($this->organisation->id.':'.$this->auroraModelData->{'Customer Poll Query Store Key'});
        $this->parsedData['poll_has_customer'] = [
            'value' => $this->auroraModelData->{'Customer Poll Reply'},
            'created_at' => $this->parseDatetime($this->auroraModelData->{'Customer Poll Date'}),
            'source_id'                => $this->organisation->id.':'.$this->auroraModelData->{'Customer Poll Key'},
            'fetched_at'               => now(),
            'last_fetched_at'          => now(),
        ];

        if ($pollOption) {
            $this->parsedData['poll_has_customer']['poll_option_id'] = $pollOption->id;
        }


    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Customer Poll Fact')
            ->where('Customer Poll Key', $id)->first();
    }
}
