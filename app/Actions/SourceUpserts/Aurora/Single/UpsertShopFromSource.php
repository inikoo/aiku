<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 29 Aug 2022 22:38:20 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */


/** @noinspection PhpUnused */


namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Models\Marketing\Shop;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertShopFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:shop {organisation_code} {organisation_source_id}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Shop
    {
        if ($shopData = $organisationSource->fetchShop($organisation_source_id)) {
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

            return $res->model;
        }

        return null;
    }


}
