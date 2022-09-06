<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:27:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Models\Marketing\Shop;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchShop extends Fetch
{


    public string $commandSignature = 'fetch:shops {organisation_code} {organisation_source_id?}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Shop
    {
        if ($shopData = $organisationSource->fetchShop($organisationSourceId)) {
            if ($shop = Shop::where('organisation_source_id', $shopData['shop']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateShop::run(
                    shop: $shop,
                    modelData: $shopData['shop']
                );
            } else {
                $res = StoreShop::run(
                    organisation: $organisationSource->organisation,
                    modelData:    $shopData['shop'],
                    addressData:  []
                );
            }
            $this->progressBar?->advance();

            return $res->model;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Key as source_id')
            ->whereIn('Store Status', ['Normal', 'ClosingDown'])
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Store Dimension')
            ->whereIn('Store Status', ['Normal', 'ClosingDown'])->count();
    }

}
