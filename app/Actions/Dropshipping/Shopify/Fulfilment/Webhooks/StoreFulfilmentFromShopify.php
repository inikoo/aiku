<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 11 Feb 2025 11:47:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment\Webhooks;

use App\Actions\Dropshipping\Shopify\Fulfilment\CancelFulfilmentOrderShopify;
use App\Actions\Dropshipping\Shopify\Fulfilment\StoreShopifyOrderFulfilment;
use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItemsToReturn;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\ShopifyUserHasProduct;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreFulfilmentFromShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, array $modelData): bool
    {
        return DB::transaction(function () use ($shopifyUser, $modelData) {
            // $deliveryAddress        = Arr::get($modelData, 'destination');
            // $countryDeliveryAddress = Country::where('code', Arr::get($deliveryAddress, 'country_code'))->first();

            if ($shopifyUser->orders()->where('shopify_fulfilment_id', Arr::get($modelData, 'id'))->exists()) {
                return false;
            }

            $shopifyProducts = collect($modelData['line_items']);

            /*        $deliveryAddress = [
                        'address_line_1'      => Arr::get($deliveryAddress, 'address1'),
                        'address_line_2'      => Arr::get($deliveryAddress, 'address2'),
                        'sorting_code'        => null,
                        'postal_code'         => Arr::get($deliveryAddress, 'zip'),
                        'dependent_locality'  => null,
                        'locality'            => Arr::get($deliveryAddress, 'city'),
                        'administrative_area' => Arr::get($deliveryAddress, 'province'),
                        'country_code'        => Arr::get($deliveryAddress, 'country_code'),
                        'country_id'          => $countryDeliveryAddress->id
                    ];*/

            $palletReturn = StorePalletReturn::make()->actionWithDropshipping($shopifyUser->customer->fulfilmentCustomer, [
                'type' => PalletReturnTypeEnum::DROPSHIPPING,
                'platform_id' => $shopifyUser->customer->platform()?->id
            ]);

            $storedItems = [];
            $itemsComplete = true;
            $orderCancelled = true;

            foreach ($shopifyProducts as $shopifyProduct) {
                $shopifyUserHasProduct = ShopifyUserHasProduct::where('shopify_user_id', $shopifyUser->id)
                    ->where('shopify_product_id', $shopifyProduct['product_id'])
                    ->first();

                $storedItems[$shopifyUserHasProduct->portfolio->item_id] = [
                    'quantity' => $shopifyProduct['quantity']
                ];

                $itemQuantity = $shopifyUserHasProduct->portfolio->item->total_quantity;
                $requiredQuantity = $shopifyProduct['quantity'];

                if ($itemQuantity < $requiredQuantity) {
                    $itemsComplete = false;
                } else {
                    $orderCancelled = false;
                }
            }

            StoreStoredItemsToReturn::make()->action($palletReturn, [
                'stored_items' => $storedItems
            ]);

            $shopifyOrder = StoreShopifyOrderFulfilment::run($shopifyUser, $palletReturn, [
                'shopify_order_id' => Arr::get($modelData, 'order_id'),
                'shopify_fulfilment_id' => Arr::get($modelData, 'id')
            ]);

            if ($shopifyOrder && $orderCancelled) {
                CancelFulfilmentOrderShopify::run($shopifyOrder, $shopifyUser);
            }

            return true;
        });
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisation($shopifyUser->organisation, $request);

        $this->handle($shopifyUser, $request->all());
    }
}
