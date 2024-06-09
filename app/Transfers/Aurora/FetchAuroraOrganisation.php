<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:10 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use App\Models\SysAdmin\Organisation;
use App\Transfers\SourceOrganisationService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrganisation
{
    use WithAuroraParsers;

    protected Organisation $organisation;
    protected ?array $parsedData;
    protected ?object $auroraModelData;
    protected SourceOrganisationService $organisationSource;

    public function __construct(SourceOrganisationService $organisationSource)
    {
        $this->organisationSource = $organisationSource;
        $this->organisation       = $organisationSource->organisation;
        $this->parsedData         = null;
        $this->auroraModelData    = null;
    }

    protected function parseModel(): void
    {
        if (App::environment('local') or App::environment('testing')) {
            /** @noinspection HttpUrlsUsage */
            $auroraURL = "http://".env('AURORA_DOMAIN', 'aurora.local');
        } else {
            $auroraURL = "https://$this->organisation->code.".env('AURORA_DOMAIN', 'aurora.systems');
        }


        $this->parsedData['organisation'] = [
            'name'        => $this->auroraModelData->{'Account Name'},
            'language_id' => $this->parseLanguageID($this->auroraModelData->{'Account Locale'}),
            'timezone_id' => $this->parseTimezoneID($this->auroraModelData->{'Account Timezone'}),
            'source'      => [
                'type'         => 'Aurora',
                'db_name'      => Arr::get($this->organisation->source, 'db_name'),
                'account_code' => $this->auroraModelData->{'Account Code'},
                'url'          => $auroraURL
            ],
            'created_at'  => $this->auroraModelData->{'Account Valid From'}
        ];
    }


    public function fetch(): ?array
    {
        $this->auroraModelData = $this->fetchData();

        if ($this->auroraModelData) {
            $this->parseModel();
        }

        return $this->parsedData;
    }


    protected function fetchData(): object|null
    {
        return DB::connection('aurora')
            ->table('Account Dimension')->first();
    }
}
