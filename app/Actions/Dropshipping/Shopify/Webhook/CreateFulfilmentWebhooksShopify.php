<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\Ordering\Order\StoreOrder;
use App\Actions\Ordering\Transaction\StoreTransaction;
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

class CreateFulfilmentWebhooksShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        $deliveryAddress        = Arr::get($modelData, 'destination');
        $countryDeliveryAddress = Country::where('code', Arr::get($deliveryAddress, 'country_code'))->first();

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
            'delivery_address' => new Address($deliveryAddress),
            'billing_address'  => new Address($deliveryAddress),
        ]);

        $shopifyProducts     = collect($modelData['line_items']);
        $productIds = collect($shopifyProducts)->pluck('product_id');

        $products = $shopifyUser->products()->whereIn('shopify_product_id', $productIds)->get()->keyBy('shopify_product_id');
        $assets = $shopifyUser->organisation->assets()->whereIn('id', $products->pluck('id'))->get()->keyBy('id');

        foreach ($shopifyProducts as $shopifyProduct) {
            $product = $products->get($shopifyProduct['product_id']);

            if ($product) {
                $asset = $assets->get($product->id);

                if ($asset) {
                    StoreTransaction::make()->action($order, $asset->historicAsset, [
                        'quantity_ordered' => $shopifyProduct['quantity'],
                    ]);
                }
            }
        }

        $shopifyUser->orders()->attach($order->id, [
            'shopify_fulfilment_id' => $modelData['id'],
            'shopify_order_id'      => $modelData['order_id'],
        ]);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisation($shopifyUser->organisation, $request);

        $this->handle($shopifyUser, $request->all());
    }
}
