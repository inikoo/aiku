<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 12 Jun 2024 13:35:32 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Api;

use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\UI\Catalogue\ShopTabsEnum;
use App\Http\Resources\Api\Dropshipping\ShopResource;
use App\Models\Catalogue\Shop;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowShop extends OrgAction
{
    use AsAction;
    use WithInertia;



    public function handle(Shop $shop): Shop
    {
        return $shop;
    }

    public function prepareForValidation(): void
    {
        if($this->shop->type!=ShopTypeEnum::DROPSHIPPING) {
            abort(404);
        }

    }


    public function asController(Shop $shop, ActionRequest $request): Shop
    {
        $this->initialisationFromShop($shop, $request)->withTab(ShopTabsEnum::values());
        return $this->handle($shop);
    }

    public function jsonResponse($shop): ShopResource
    {
        return ShopResource::make($shop);
    }

}
