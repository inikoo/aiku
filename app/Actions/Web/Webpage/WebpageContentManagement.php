<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 19 Jun 2024 12:32:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Web\Webpage;

use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Web\Webpage;
use Lorisleiva\Actions\ActionRequest;

trait WebpageContentManagement
{
    public function asController(Webpage $webpage, ActionRequest $request): Webpage
    {
        if ($webpage->website->shop->type == ShopTypeEnum::FULFILMENT) {
            $this->scope = $webpage->website->shop->fulfilment;
            $this->initialisationFromFulfilment($this->scope, $request);
        } else {
            $this->scope = $webpage->website->shop;
            $this->initialisationFromShop($webpage->website->shop, $request);
        }

        return $this->handle($webpage, $this->validatedData);
    }
}
