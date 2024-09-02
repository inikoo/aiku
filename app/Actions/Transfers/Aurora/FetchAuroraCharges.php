<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jul 2024 11:35:34 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Catalogue\Charge\StoreCharge;
use App\Actions\Catalogue\Charge\UpdateCharge;
use App\Models\Catalogue\Charge;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraCharges extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:charges {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Charge
    {
        if ($chargeData = $organisationSource->fetchCharge($organisationSourceId)) {

            if ($charge = Charge::where('source_id', $chargeData['charge']['source_id'])->first()) {
                $charge = UpdateCharge::make()->action(
                    charge: $charge,
                    modelData: $chargeData['charge'],
                    strict: false,
                    audit:false
                );
            } else {
                $charge = StoreCharge::make()->action(
                    shop: $chargeData['shop'],
                    modelData: $chargeData['charge'],
                    strict: false,
                );


            }

            return $charge;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Charge Dimension')
            ->select('Charge Key as source_id')
            ->where('Charge Scope', '!=', 'Insurance')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Charge Dimension')
            ->where('Charge Scope', '!=', 'Insurance')->count();
    }


}
