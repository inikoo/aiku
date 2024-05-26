<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Ordering\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithFixedAddressActions;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;

class UpdateOrder extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;

    private Order $order;

    public function handle(Order $order, array $modelData): Order
    {
        /** @var Address $billingAddressData */
        $billingAddressData = Arr::get($modelData, 'billing_address');
        data_forget($modelData, 'billing_address');
        /** @var Address $deliveryAddressData */
        $deliveryAddressData = Arr::get($modelData, 'delivery_address');
        data_forget($modelData, 'delivery_address');

        $order = $this->update($order, $modelData, ['data']);

        if ($billingAddressData) {
            if ($order->billing_locked) {
                if ($order->billingAddress->is_fixed) {
                    $order = $this->updateFixedAddress(
                        $order,
                        $order->billingAddress,
                        $billingAddressData,
                        'Ordering',
                        'billing',
                        'billing_address_id'
                    );
                } else {
                    // todo remove non fixed address
                    $order = $this->createFixedAddress($order, $billingAddressData, 'Ordering', 'billing', 'billing_address_id');
                }
            } else {
                UpdateAddress::run($order->billingAddress, $billingAddressData->toArray());
            }
        }
        if ($deliveryAddressData) {
            if ($order->delivery_locked) {
                if ($order->deliveryAddress->is_fixed) {
                    $order = $this->updateFixedAddress(
                        $order,
                        $order->deliveryAddress,
                        $deliveryAddressData,
                        'Ordering',
                        'delivery',
                        'delivery_address_id'
                    );
                } else {
                    // todo remove non fixed address
                    $order = $this->createFixedAddress($order, $deliveryAddressData, 'Ordering', 'delivery', 'delivery_address_id');
                }
            } else {
                UpdateAddress::run($order->deliveryAddress, $deliveryAddressData->toArray());
            }
        }


        OrderHydrateUniversalSearch::dispatch($order);

        return $order;
    }

    public function rules(): array
    {
        $rules = [
            'number'           => [
                'sometimes',
                'string',
                'max:64',
                new IUnique(
                    table: 'orders',
                    extraConditions: [
                        ['column' => 'shop_id', 'value' => $this->shop->id],
                        ['column' => 'id', 'value' => $this->order->id, 'operator' => '!=']
                    ]
                ),
            ],
            'date'             => ['sometimes', 'required', 'date'],
            'billing_address'  => ['sometimes', 'required', new ValidAddress()],
            'delivery_address' => ['sometimes', 'required', new ValidAddress()],
            'billing_locked'   => ['sometimes', 'boolean'],
            'delivery_locked'  => ['sometimes', 'boolean'],
        ];

        if (!$this->strict) {
            $rules['number'] = ['sometimes', 'string', 'max:64'];
        }

        return $rules;
    }

    public function action(Order $order, array $modelData, bool $strict = true, int $hydratorsDelay = 0): Order
    {
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->order          = $order;

        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $this->validatedData);
    }
}
