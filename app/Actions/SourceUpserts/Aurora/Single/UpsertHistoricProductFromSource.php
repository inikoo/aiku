<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:14:57 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


/** @noinspection PhpUnused */

namespace App\Actions\SourceUpserts\Aurora\Single;

use App\Actions\Delivery\DeliveryNote\StoreDeliveryNote;
use App\Actions\Delivery\DeliveryNote\UpdateDeliveryNote;
use App\Actions\Marketing\HistoricProduct\StoreHistoricProduct;
use App\Actions\Marketing\HistoricProduct\UpdateHistoricProduct;
use App\Models\Delivery\DeliveryNote;
use App\Models\Marketing\HistoricProduct;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertHistoricProductFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:historic-product {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?HistoricProduct
    {
        if ($historicProductData = $organisationSource->fetchHistoricProduct($organisation_source_id)) {
            if ($historicProduct = HistoricProduct::where('organisation_source_id', $historicProductData['historic_product']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateHistoricProduct::run(
                    historicProduct: $historicProduct,
                    modelData:       $historicProductData['historic_product'],
                );
            } else {
                $res = StoreHistoricProduct::run(
                    product:   $historicProductData['product'],
                    modelData: $historicProductData['historic_product']
                );
            }


            return $res->model;
        }


        return null;
    }


}
