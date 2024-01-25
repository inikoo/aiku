<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jul 2023 12:19:19 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Fulfilment\Pallet\StorePallet;
use App\Actions\Fulfilment\Pallet\UpdatePallet;

use App\Models\Fulfilment\Pallet;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchPallets extends FetchAction
{
    public string $commandSignature = 'fetch:pallets {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Pallet
    {
        if ($palletData = $organisationSource->fetchPallet($organisationSourceId)) {
            if ($pallet = Pallet::withTrashed()->where('source_id', $palletData['pallet']['source_id'])
                ->first()) {
                $pallet = UpdatePallet::run(
                    pallet: $pallet,
                    modelData: $palletData['pallet']
                );
            } else {
                $pallet = StorePallet::make()->action(
                    customer: $palletData['customer'],
                    modelData: $palletData['pallet'],
                );
            }


            return $pallet;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Fulfilment Asset Dimension')
            ->select('Fulfilment Asset Key as source_id')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Fulfilment Asset Dimension')->count();
    }
}
