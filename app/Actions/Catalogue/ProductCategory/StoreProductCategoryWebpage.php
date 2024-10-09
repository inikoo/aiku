<?php
/*
 * author Arya Permana - Kirin
 * created on 07-10-2024-14h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\ProductCategory;

use App\Actions\OrgAction;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Enums\Catalogue\ProductCategory\ProductCategoryTypeEnum;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Catalogue\ProductCategory;
use App\Models\Web\Webpage;

class StoreProductCategoryWebpage extends OrgAction
{
    public function handle(ProductCategory $productCategory): Webpage
    {

        if ($productCategory->type == ProductCategoryTypeEnum::FAMILY) {
            $webpageData = [
                'code'  => $productCategory->code,
                'url'   => strtolower($productCategory->code),
                'purpose'   => WebpagePurposeEnum::FAMILY,
                'type'      => WebpageTypeEnum::SHOP,
                'model_type'    => class_basename($productCategory),
                'model_id'     => $productCategory->id
            ];
        } else {
            $webpageData = [
                'code'  => $productCategory->code,
                'url'   => strtolower($productCategory->code),
                'purpose'   => WebpagePurposeEnum::DEPARTMENT,
                'type'      => WebpageTypeEnum::SHOP,
                'model_type'    => class_basename($productCategory),
                'model_id'     => $productCategory->id
            ];
        }

        return StoreWebpage::make()->action(
            $productCategory->shop->website,
            $webpageData
        );
    }

    public function asController(ProductCategory $productCategory): Webpage
    {
        $this->initialisationFromShop($productCategory->shop, []);
        return $this->handle($productCategory);
    }
}