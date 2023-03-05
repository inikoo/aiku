<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 27 Nov 2022 13:12:35 Central Indonesia Time, Ubud, Bali, Indonesia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentOrder;

use App\Actions\fromIris;
use App\Http\Resources\Fulfilment\FulfilmentOrderResource;
use App\Models\Fulfilment\FulfilmentOrder;
use App\Models\Helpers\Address;
use App\Models\Marketing\Product;
use App\Models\Web\WebUser;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class CreateFulfilmentOrderFromIris extends fromIris
{
    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            [
                'number'                        => ['required', 'alpha_dash'],
                'delivery_address'              => ['required', 'array:address_line_1,address_line_2,sorting_code,postal_code,locality,dependant_locality,administrative_area,country_code'],
                'delivery_address.country_code' => ['required', 'string', 'size:2', 'exists:countries,code'],
                'items'                         => ['required', 'array'],


            ]
        );
    }


    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(WebUser $webUser, array $modelData): ?FulfilmentOrder
    {
        if ($webUser->customer->fulfilmentOrders()->where('number', Arr::get($modelData, 'number'))->exists()) {
            throw  ValidationException::withMessages([
                                                         'code' => 'Order number already exists'
                                                     ]);
        }


        $orderData = Arr::only($modelData, ['number']);

        $deliveryAddress = new Address([
                                           'address_line_1'      => Arr::get($modelData, 'delivery_address.delivery_address.address_line_1'),
                                           'address_line_2'      => Arr::get($modelData, 'delivery_address.address_line_2'),
                                           'sorting_code'        => Arr::get($modelData, 'delivery_address.sorting_code'),
                                           'postal_code'         => Arr::get($modelData, 'delivery_address.postal_code'),
                                           'locality'            => Arr::get($modelData, 'delivery_address.locality'),
                                           'dependant_locality'  => Arr::get($modelData, 'delivery_address.dependant_locality'),
                                           'administrative_area' => Arr::get($modelData, 'delivery_address.administrative_area'),
                                           'country_code'        => Arr::get($modelData, 'delivery_address.country_code')
                                       ]);

        $customerProducts = $webUser->customer->products()->pluck('id');


        $items = [];

        foreach (Arr::get($modelData, 'items') as $productID => $item) {
            if (!$customerProducts->contains($productID)) {
                throw  ValidationException::withMessages([
                                                             'items' => 'Invalid item id: '.$productID
                                                         ]);
            }

            $product=Product::find($productID);

            $items[] = [
                'item_type' => 'HistoricProduct',
                'item_id'   => $product->current_historic_product_id,
                'quantity'  => Arr::get($item, 'quantity')
            ];
        }

        return StoreFulfilmentOrder::run(
            parent:          $webUser->customer,
            modelData:       $orderData,
            deliveryAddress: $deliveryAddress,
            items:           $items
        );
    }

    public function jsonResponse(?FulfilmentOrder $fulfilmentOrder): FulfilmentOrderResource
    {
        if (!$fulfilmentOrder) {
            abort(500, 'Could not create order');
        }

        return new FulfilmentOrderResource($fulfilmentOrder);
    }
}
