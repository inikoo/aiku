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

        $productIds     = collect($modelData['line_items'])->pluck('product_id');
        $historicAssets = $shopifyUser->organisation->assets()
            ->whereIn('id', function ($query) use ($productIds) {
                $query->select('asset_id')
                    ->from('products')
                    ->whereIn('id', $productIds);
            })->chunkMap(function ($asset) {
                return $asset->historicAsset;
            }, 100);

        foreach ($historicAssets as $historicAsset) {
            StoreTransaction::make()->action($order, $historicAsset, []);
        }

        $shopifyUser->orders()->attach($order->id, [
            'shopify_fulfilment_id' => $modelData['id'],
            'shopify_order_id'      => $modelData['order_id'],
        ]);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisation($shopifyUser->organisation, $request);

        $this->handle($shopifyUser, $this->validatedData);
    }
}
