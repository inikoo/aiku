<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 02 Jun 2024 19:38:02 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\HistoricProductVariant;

use App\Actions\Catalogue\ProductVariant\Hydrators\ProductVariantHydrateHistoricProductVariants;
use App\Models\Catalogue\HistoricProductVariant;
use App\Models\Catalogue\ProductVariant;

use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreHistoricProductVariant
{
    use AsAction;

    public function handle(ProductVariant $productVariant, array $modelData = []): HistoricProductVariant
    {
        data_set($historicProductVariantData, 'code', $productVariant->code);
        data_set($historicProductVariantData, 'name', $productVariant->name);
        data_set($historicProductVariantData, 'price', $productVariant->price);
        data_set($historicProductVariantData, 'ratio', $productVariant->ratio);
        data_set($historicProductVariantData, 'unit', $productVariant->unit);
        data_set($historicProductVariantData, 'units', $productVariant->units);
        data_set($historicProductVariantData, 'currency_id', $productVariant->currency_id);


        if (Arr::get($modelData, 'created_at')) {
            $historicProductVariantData['created_at'] = Arr::get($modelData, 'created_at');
        } else {
            $historicProductVariantData['created_at'] = $productVariant->created_at;
        }
        if (Arr::get($modelData, 'deleted_at')) {
            $historicProductVariantData['deleted_at'] = Arr::get($modelData, 'deleted_at');
        }


        data_set($historicProductVariantData, 'organisation_id', $productVariant->organisation_id);
        data_set($historicProductVariantData, 'group_id', $productVariant->group_id);
        data_set($historicProductVariantData, 'asset_id', $productVariant->asset_id);


        /** @var HistoricProductVariant $historicProductVariant */
        $historicProductVariant = $productVariant->historicProductVariants()->create($historicProductVariantData);
        $historicProductVariant->stats()->create();
        $historicProductVariant->salesIntervals()->create();


        ProductVariantHydrateHistoricProductVariants::dispatch($productVariant);

        return $historicProductVariant;
    }
}
