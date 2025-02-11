<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 11 Feb 2025 11:47:12 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment\Webhooks;

use App\Actions\Fulfilment\PalletReturn\StorePalletReturn;
use App\Actions\Fulfilment\StoredItem\StoreStoredItemsToReturn;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Fulfilment\PalletReturn\PalletReturnTypeEnum;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\ShopifyUserHasProduct;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreFulfilmentFromShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        // $deliveryAddress        = Arr::get($modelData, 'destination');
        // $countryDeliveryAddress = Country::where('code', Arr::get($deliveryAddress, 'country_code'))->first();

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

        $palletReturn = StorePalletReturn::make()->actionWithStoredItems($shopifyUser->customer->fulfilmentCustomer, [
            'type' => PalletReturnTypeEnum::STORED_ITEM
        ]);

        $storedItems = [];
        foreach ($shopifyProducts as $shopifyProduct) {
            $shopifyUserHasProduct = ShopifyUserHasProduct::where('shopify_user_id', $shopifyUser->id)
                ->where('shopify_product_id', $shopifyProduct['product_id'])
                ->first();

            $storedItems[$shopifyUserHasProduct->product->id] = [
                'quantity' => $shopifyProduct['quantity']
            ];
        }

        StoreStoredItemsToReturn::make()->action($palletReturn, [
            'stored_items' => $storedItems
        ]);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisation($shopifyUser->organisation, $request);

        $this->handle($shopifyUser, $request->all());
    }
}
