<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 20:18:44 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Services\Organisation\Wowsbar;

use App\Models\SysAdmin\Organisation;
use App\Services\Organisation\SourceOrganisationService;

class FetchWowsbar
{
    protected Organisation $organisation;
    protected ?array $parsedData;
    protected ?object $wowModelData;
    protected SourceOrganisationService $organisationSource;

    public function __construct(SourceOrganisationService $organisationSource)
    {
        $this->organisationSource    = $organisationSource;
        $this->organisation          = $organisationSource->organisation;
        $this->parsedData            = null;
        $this->wowModelData          = null;
    }

    public function fetch(int $id): ?array
    {
        $this->wowModelData = $this->fetchData($id);

        if ($this->wowModelData) {
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
