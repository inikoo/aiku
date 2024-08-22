<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:19:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;

use App\Models\Fulfilment\Pallet;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraPallets extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:pallets {organisations?*} {--s|source_id=} {--d|db_suffix=} {--N|only_new : Fetch only new} ';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Pallet
    {
        if ($palletData = $organisationSource->fetchPallet($organisationSourceId)) {


            if ($pallet = Pallet::withTrashed()->where('source_id', $palletData['pallet']['source_id'])
                ->first()) {
                $pallet = UpdatePallet::make()->action(
                    pallet: $pallet,
                    modelData: $palletData['pallet'],
                    audit:false
                );
            } else {
                $pallet = StorePallet::make()->action(
                    fulfilmentCustomer: $palletData['customer']->fulfilmentCustomer,
                    modelData: $palletData['pallet'],
                );
            }


            $sourceData = explode(':', $pallet->source_id);
            DB::connection('aurora')->table('Fulfilment Asset Dimension')
                ->where('Fulfilment Asset Key', $sourceData[1])
                ->update(['aiku_id' => $pallet->id]);

            return $pallet;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query= DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->select('Fulfilment Asset Key as source_id')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query;

    }


    public function count(): ?int
    {
        $query= DB::connection('aurora')->table('Fulfilment Asset Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        return $query->count();
    }
}
