<?php
/*
 * author Arya Permana - Kirin
 * created on 07-10-2024-14h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Collection;

use App\Actions\OrgAction;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Catalogue\Collection;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;

class StoreCollectionWebpage extends OrgAction
{
    public function handle(Collection $collection): Webpage
    {
        $webpageData = [
                'code'  => $collection->code,
                'url'   => strtolower($product->code),
                'purpose'   => WebpagePurposeEnum::COLLECTION,
                'type'      => WebpageTypeEnum::SHOP,
                'model_type'    => class_basename($collection),
                'model_id'     => $collection->id 
            ];
        $webpage = StoreWebpage::make()->action(
            $collection->shop->website,
            $webpageData
        );

        return $webpage;
    }

    public function asController(Collection $collection)
    {
        $this->initialisationFromShop($collection->shop, []);
        return $this->handle($collection);
    }
}

