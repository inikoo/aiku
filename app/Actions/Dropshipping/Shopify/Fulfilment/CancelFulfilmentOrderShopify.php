<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Feb 2025 15:06:31 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment;

use App\Actions\Fulfilment\PalletReturn\DeletePalletReturn;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\ShopifyUserHasFulfilment;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CancelFulfilmentOrderShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    /**
     * @throws \Throwable
     */
    public function handle(ShopifyUserHasFulfilment $shopifyUserHasFulfilment, ShopifyUser $shopifyUser): void
    {
        $client = $shopifyUser->api()->getRestClient();

        $response = $client->request('POST', "/admin/api/2024-04/fulfillments/$shopifyUserHasFulfilment->shopify_fulfilment_id/cancel.json");
        /** @var \App\Models\Fulfilment\PalletReturn $palletReturn */
        $palletReturn = $shopifyUserHasFulfilment->model;

        if (!$response['errors']) {
            DeletePalletReturn::make()->action($palletReturn, [
                'delete_comment' => __('Your order doesn\'t have enough quantity in warehouse.')
            ]);
        }

        if ($response['body'] == 'Not Found') {
            throw ValidationException::withMessages(['messages' => __('You dont have any products')]);
        }
    }
}
