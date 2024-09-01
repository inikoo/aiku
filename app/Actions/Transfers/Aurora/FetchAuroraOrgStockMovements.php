<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 31 Aug 2024 12:14:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Inventory\OrgStockMovement\StoreOrgStockMovement;
use App\Models\Inventory\OrgStockMovement;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraOrgStockMovements extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:stock_movements {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?OrgStockMovement
    {
        if ($orgStockMovementData = $organisationSource->fetchOrgStockMovement($organisationSourceId)) {
            //  print_r($orgStockMovementData);
            if ($orgStockMovement = OrgStockMovement::where('source_id', $orgStockMovementData['orgStockMovement']['source_id'])
                ->first()) {
                //
            } else {
                //


                //  try {
                $orgStockMovement = StoreOrgStockMovement::make()->action(
                    orgStock: $orgStockMovementData['orgStock'],
                    location: $orgStockMovementData['location'],
                    modelData: $orgStockMovementData['orgStockMovement'],
                    hydratorsDelay: 60,
                    strict: false
                );

                //                    $this->recordNew($organisationSource);
                //                } catch (Exception $e) {
                //                    $this->recordError($organisationSource, $e, $orgStockMovementData['orgStockMovement'], 'orgStockMovement', 'store');
                //
                //                    return null;
                //                }
            }


            return $orgStockMovement;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Inventory Transaction Fact')
            ->select('Inventory Transaction Key as source_id')
            ->whereIn('Inventory Transaction Record Type', ['Movement', 'Helper'])
            ->orderBy('Date');
    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Inventory Transaction Fact')
            ->whereIn('Inventory Transaction Record Type', ['Movement', 'Helper'])
            ->count();
    }
}
