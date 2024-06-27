<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 25 Jun 2024 15:50:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Product;

use App\Actions\Catalogue\WithUploadProductImage;
use App\Actions\OrgAction;
use App\Actions\Traits\Authorisations\HasWebAuthorisation;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Catalogue\Product;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class UploadImagesToProduct extends OrgAction
{
    use WithUploadProductImage;
    use HasWebAuthorisation;


    public function asController(Product $product, ActionRequest $request): Collection
    {
        if($product->shop->type==ShopTypeEnum::FULFILMENT) {
            $this->scope = $product->shop->fulfilment;
            $this->initialisationFromFulfilment($this->scope, $request);

        } else {
            $this->scope = $product->shop;
            $this->initialisationFromShop($this->scope, $request);

        }

        return $this->handle($product, 'image', $this->validatedData);
    }


}
