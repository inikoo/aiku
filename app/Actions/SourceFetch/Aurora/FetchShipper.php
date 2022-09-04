<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 02:12:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Delivery\Shipper\StoreShipper;
use App\Models\Delivery\Shipper;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchShipper extends FetchModel
{


    public string $commandSignature = 'fetch:shippers {organisation_code} {organisation_source_id?*}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Shipper
    {
        if ($shipperData = $organisationSource->fetchShipper($organisation_source_id)) {
            $shipper = Shipper::where('code', $shipperData['shipper']['code'])
                ->first();

            if (!$shipper) {
                $res     = StoreShipper::run(modelData: $shipperData['shipper']);
                $shipper = $res->model;
            }

            $organisationSource->organisation->shippers()->attach($shipper->id);
            $this->progressBar?->advance();

            return $shipper;
        }

        return null;
    }


    function fetchAll(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Shipper Dimension')
                ->select('Shipper Key')
                ->where('Shipper Active', 'Yes')
                ->get() as $auroraData
        ) {
            $this->handle($organisationSource, $auroraData->{'Shipper Key'});
        }
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Shipper Dimension')
            ->where('Shipper Active', 'Yes')
            ->count();
    }


}
