<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 10 Feb 2025 16:53:36 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Fulfilment\PalletReturn;
use App\Models\ShopifyUserHasFulfilment;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreShopifyOrderFulfilment extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, PalletReturn $model, array $modelData)
    {
        $shopifyUser->orders()->attach($model->id, [
            'shopify_user_id' => $shopifyUser->id,
            'model_type' => class_basename($model),
            'model_id' => $model->id,
            'shopify_order_id' => Arr::get($modelData, 'shopify_order_id'),
            'shopify_fulfilment_id' => Arr::get($modelData, 'shopify_fulfilment_id')
        ]);

        return ShopifyUserHasFulfilment::where('shopify_fulfilment_id', Arr::get($modelData, 'shopify_fulfilment_id'))->first();
    }
}
