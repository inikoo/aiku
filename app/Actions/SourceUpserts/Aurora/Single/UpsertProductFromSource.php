<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Models\Marketing\Product;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertProductFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:product {organisation_code} {organisation_source_id}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Product
    {
        if ($productData = $organisationSource->fetchProduct($organisation_source_id)) {
            if ($product = Product::where('organisation_source_id', $productData['product']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateProduct::run(
                    product:   $product,
                    modelData: $productData['product'],
                );
            } else {
                $res = StoreProduct::run(
                    shop:      $productData['shop'],
                    modelData: $productData['product']
                );

            }
            /** @var Product $product */
            $product=$res->model;
            $tradeUnits= $organisationSource->fetchProductStocks($organisation_source_id);
            $product->tradeUnits()->sync($tradeUnits['product_stocks']);

            return $product;
        }


        return null;
    }


}
