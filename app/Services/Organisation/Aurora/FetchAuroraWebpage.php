<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 16 Jan 2024 11:39:27 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Services\Organisation\Aurora;

use App\Enums\Web\Website\WebsiteEngineEnum;
use App\Enums\Web\Website\WebsiteStateEnum;
use Illuminate\Support\Facades\DB;

class FetchAuroraWebpage extends FetchAurora
{
    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        $this->parseModel();

        return $this->parsedData;
    }

    protected function parseModel(): void
    {
        $this->parsedData['website'] = $this->parseWebsite($this->organisation->id.':'.$this->auroraModelData->{'Website Store Key'});

        $status = match ($this->auroraModelData->{'Website Status'}) {
            'Active' => WebsiteStateEnum::LIVE,
            'Closed' => WebsiteStateEnum::CLOSED,
            default  => WebsiteStateEnum::IN_PROCESS,
        };


        $domain = preg_replace('/^www\./', '', strtolower($this->auroraModelData->{'Website URL'}));


        $this->parsedData['website'] =
            [
                'engine'      => WebsiteEngineEnum::AURORA,
                'name'        => $this->auroraModelData->{'Website Name'},
                'code'        => $this->auroraModelData->code,
                'domain'      => $domain,
                'state'       => $status,
                'launched_at' => $this->parseDate($this->auroraModelData->{'Website Launched'}),
                'created_at'  => $this->parseDate($this->auroraModelData->{'Website From'}),
                'source_id'   => $this->organisation->id.':'.$this->auroraModelData->{'Website Key'},

            ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Website Dimension')
            ->where('Website Key', $id)->first();
    }
}
