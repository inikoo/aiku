<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 20:18:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Aurora;

use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\SourceOrganisationService;

class FetchAurora
{
    use WithAuroraParsers;


    protected Organisation $organisation;
    protected ?array $parsedData;
    protected ?object $auroraModelData;
    protected SourceOrganisationService $organisationSource;

    public function __construct(SourceOrganisationService $organisationSource)
    {
        $this->tenantSource    = $organisationSource;
        $this->tenant          = $organisationSource->tenant;
        $this->parsedData      = null;
        $this->auroraModelData = null;
    }

    public function fetch(int $id): ?array
    {
        $this->auroraModelData = $this->fetchData($id);

        if ($this->auroraModelData) {
            $this->parseModel();
        }

        return $this->parsedData;
    }

    protected function fetchData($id): object|null
    {
        return null;
    }

    protected function parseModel(): void
    {
    }
}
