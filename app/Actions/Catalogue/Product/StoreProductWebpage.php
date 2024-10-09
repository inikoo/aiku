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
use Illuminate\Support\Facades\Redirect;

class StoreProductWebpage extends OrgAction
{
    public function handle(Product $product): Webpage
    {
        $webpageData = [
            'code'       => $product->code,
            'url'        => strtolower($product->code),
            'purpose'    => WebpagePurposeEnum::PRODUCT,
            'type'       => WebpageTypeEnum::SHOP,
            'model_type' => class_basename($product),
            'model_id'   => $product->id
        ];

        $webpage = StoreWebpage::make()->action(
            $product->shop->website,
            $webpageData
        );

        return $webpage;
    }

    public function htmlResponse(Webpage $webpage)
    {
        return Redirect::route(
            'grp.org.shops.show.web.webpages.show',
            [
            'organisation' => $webpage->organisation->slug,
            'shop'         => $webpage->shop->slug,
            'website'      => $webpage->website->slug,
            'webpage'      => $webpage->slug,
        ]
        );
    }

    public function asController(Product $product): Webpage
    {
        $this->initialisationFromShop($product->shop, []);

        return $this->handle($product);
    }
}
