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
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use Illuminate\Support\Arr;
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
        $billingAddress  = Arr::get($modelData, 'billing_address');
        $deliveryAddress = Arr::get($modelData, 'shipping_address');

        $countryBillingAddress  = Country::where('code', Arr::get($billingAddress, 'country_code'))->first();
        $countryDeliveryAddress = Country::where('code', Arr::get($deliveryAddress, 'country_code'))->first();

        $billingAddress = [
            'address_line_1'      => Arr::get($billingAddress, 'address1'),
            'address_line_2'      => Arr::get($billingAddress, 'address2'),
            'sorting_code'        => null,
            'postal_code'         => Arr::get($billingAddress, 'zip'),
            'dependent_locality'  => null,
            'locality'            => Arr::get($billingAddress, 'city'),
            'administrative_area' => Arr::get($billingAddress, 'province'),
            'country_code'        => Arr::get($billingAddress, 'country_code'),
            'country_id'          => $countryBillingAddress->id
        ];

        $deliveryAddress = [
            'address_line_1'      => Arr::get($deliveryAddress, 'address1'),
            'address_line_2'      => Arr::get($deliveryAddress, 'address2'),
            'sorting_code'        => null,
            'postal_code'         => Arr::get($deliveryAddress, 'zip'),
            'dependent_locality'  => null,
            'locality'            => Arr::get($deliveryAddress, 'city'),
            'administrative_area' => Arr::get($deliveryAddress, 'province'),
            'country_code'        => Arr::get($deliveryAddress, 'country_code'),
            'country_id'          => $countryDeliveryAddress->id
        ];

        $order = StoreOrder::make()->action($shopifyUser->customer, [
            'reference'        => Str::random(8),
            'date'             => $modelData['created_at'],
            'billing_address'  => new Address($billingAddress),
            'delivery_address' => new Address($deliveryAddress)
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
