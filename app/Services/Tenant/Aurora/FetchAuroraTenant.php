<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 29 Apr 2023 15:19:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Services\Tenant\Aurora;

use App\Models\Tenancy\Tenant;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class FetchAuroraTenant
{
    use WithAuroraParsers;

    protected Tenant $tenant;
    protected ?array $parsedData;
    protected ?object $auroraModelData;
    protected SourceTenantService $tenantSource;

    public function __construct(SourceTenantService $tenantSource)
    {
        $this->tenantSource    = $tenantSource;
        $this->tenant          = $tenantSource->tenant;
        $this->parsedData      = null;
        $this->auroraModelData = null;
    }

    protected function parseModel(): void
    {
        if (App::environment('local') or App::environment('testing')) {
            /** @noinspection HttpUrlsUsage */
            $auroraURL = "http://".env('AURORA_DOMAIN', 'aurora.local');
        } else {
            $auroraURL = "https://$this->tenant->code.".env('AURORA_DOMAIN', 'aurora.systems');
        }


        $this->parsedData['tenant'] = [
            'name'        => $this->auroraModelData->{'Account Name'},
            'language_id' => $this->parseLanguageID($this->auroraModelData->{'Account Locale'}),
            'timezone_id' => $this->parseTimezoneID($this->auroraModelData->{'Account Timezone'}),
            'source'      => [
                'type'         => 'Aurora',
                'db_name'      => Arr::get($this->tenant->source, 'db_name'),
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
