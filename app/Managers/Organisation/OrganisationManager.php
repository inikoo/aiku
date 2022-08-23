<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 23 Aug 2022 02:51:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Managers\Organisation;

use App\Services\Organisation\AuroraOrganisationService;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Support\Arr;

class OrganisationManager implements SourceOrganisationManager{

    private array $organisations = [];



    /**
     * @throws \Exception
     * @uses createAuroraOrganisationService
     */
    public function make($name): SourceOrganisationService
    {
        $service = Arr::get($this->organisations, $name);
        if ($service) {
            return $service;
        }

        $createMethod = 'create' . ucfirst($name) . 'OrganisationService';
        if (!method_exists($this, $createMethod)) {
            throw new Exception("Organisation source $name is not supported");
        }
        $service = $this->{$createMethod}();
        $this->organisations[$name] = $service;
        return $service;

    }

    private function createAuroraOrganisationService(): AuroraOrganisationService
    {
        return new AuroraOrganisationService();
    }

}

