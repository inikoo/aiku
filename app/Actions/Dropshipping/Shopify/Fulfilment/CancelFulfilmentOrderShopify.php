<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 17 Feb 2025 15:06:31 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment;

use App\Actions\Fulfilment\PalletReturn\CancelPalletReturn;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Dropshipping\ShopifyFulfilmentStateEnum;
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

        $response = $client->request('POST', "/admin/api/2024-04/fulfillment_orders/$shopifyUserHasFulfilment->shopify_fulfilment_id/hold.json", [
            'fulfillment_hold' => [
                'reason' => 'inventory_out_of_stock',
                'reason_notes' => __('Not enough inventory to complete this work.')
            ]
        ]);
        /** @var \App\Models\Fulfilment\PalletReturn $palletReturn */
        $palletReturn = $shopifyUserHasFulfilment->model;

        if (!$response['errors']) {
            CancelPalletReturn::make()->action($palletReturn->fulfilmentCustomer, $palletReturn);

            $this->update($shopifyUserHasFulfilment, [
                'state' => ShopifyFulfilmentStateEnum::INCOMPLETE
            ]);
        }

        if ($response['body'] == 'Not Found') {
            throw ValidationException::withMessages(['messages' => __('You dont have any products')]);
        }
    }
}
