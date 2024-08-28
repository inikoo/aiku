<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Helpers\Country;
use Illuminate\Support\Str;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class CreateOrderWebhooksShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        $billingAddress = $modelData['billing_address'];

        $country = Country::where('code', $billingAddress['country_code'])->first();
        data_set($billingAddress, 'country_id', $country->id);

        $order = StoreOrder::make()->action($shopifyUser->customer, [
            'reference'       => Str::random(8),
            'date'            => $modelData['created_at'],
            'billing_address' => $billingAddress
        ]);

        $shopifyUser->orders()->attach($order->id, [
            'shopify_order_id' => $modelData['id']
        ]);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->handle($shopifyUser, $request->all());
    }
}