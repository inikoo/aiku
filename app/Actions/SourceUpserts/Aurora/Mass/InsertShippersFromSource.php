<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 13:36:18 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Mass;

use App\Actions\Delivery\Shipper\StoreShipper;
use App\Managers\Organisation\SourceOrganisationManager;
use App\Models\Delivery\Shipper;
use App\Models\Organisations\Organisation;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property \App\Models\Organisations\Organisation $organisation
 * @property \App\Models\Delivery\Shipper $shipper
 */
class InsertShippersFromSource
{
    use AsAction;
    use WithMassFromSourceCommand;

    public string $commandSignature = 'source-update:shippers {organisation_code} {scopes?*}';


    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \Exception
     */
    #[NoReturn] public function handle(Organisation $organisation, array|null $scopes = null): void
    {
        $this->organisation = $organisation;


        $validScopes = ['insertShipper'];

        $organisationSource = app(SourceOrganisationManager::class)->make($this->organisation->type);
        $organisationSource->initialisation($this->organisation);

        foreach (
            DB::connection('aurora')
                ->table('Shipper Dimension')
                ->select('Shipper Key')
                ->where('Shipper Active', 'Yes')
                ->get() as $auroraData
        ) {
            $shipperData = $organisationSource->fetchShipper($auroraData->{'Shipper Key'});


            if ($scopes == null) {
                $scopes = $validScopes;
            }

            foreach ($scopes as $scope) {
                if (!method_exists($this, $scope)) {
                    throw new Exception("Scope $scope is not supported");
                }
                $this->{$scope}($shipperData);
            }
        }
    }

    protected function insertShipper($shipperData): void
    {
        if ($shipper = Shipper::where('code', $shipperData['shipper']['code'])
            ->first()) {
            $this->shipper =$shipper;
        } else {
            $res = StoreShipper::run($shipperData['shipper']);
            $this->shipper = $res->model;
        }

        $this->organisation->shippers()->attach($this->shipper->id);

    }


}
