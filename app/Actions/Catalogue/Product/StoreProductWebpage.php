<?php
/*
 * author Arya Permana - Kirin
 * created on 07-10-2024-14h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Catalogue\Product;

use App\Actions\OrgAction;
use App\Actions\Web\Webpage\StoreWebpage;
use App\Enums\Web\Webpage\WebpagePurposeEnum;
use App\Enums\Web\Webpage\WebpageTypeEnum;
use App\Models\Catalogue\Product;
use App\Models\Web\Webpage;

class StoreProductWebpage extends OrgAction
{
    public function handle(Product $product): Webpage
    {
        $webpageData = [
            'code'  => $product->code,
            'url'   => $product->code,
            'purpose'   => WebpagePurposeEnum::PRODUCT,
            'type'      => WebpageTypeEnum::SHOP
        ];
        $webpage = StoreWebpage::make()->action(
            $product->shop->website,
            $webpageData
        );

        return $webpage;
    }

    public function asController(Product $product)
    {
        $this->initialisationFromShop($product->shop, []);
        return $this->handle($product);
    }
}

