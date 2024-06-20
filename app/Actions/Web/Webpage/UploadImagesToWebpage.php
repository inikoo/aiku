<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 16:37:22 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Actions\OrgAction;
use App\Actions\Web\WithUploadImage;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Web\Webpage;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;

class UploadImagesToWebpage extends OrgAction
{
    use WithUploadImage;



    public function asController(Webpage $webpage, ActionRequest $request): Collection
    {

        if($webpage->shop->type==ShopTypeEnum::FULFILMENT) {
            $this->scope = $webpage->shop->fulfilment;
            $this->initialisationFromFulfilment($this->scope, $request);

        } else {
            $this->scope = $webpage->shop;
            $this->initialisationFromShop($this->scope, $request);

        }


        return $this->handle($webpage, 'header', $this->validatedData);
    }


}
