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
use App\Actions\Traits\WithModelAddressActions;
use App\Models\Helpers\Address;
use App\Models\Helpers\Country;
use App\Models\Ordering\Order;
use App\Rules\IUnique;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateOrder extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;
    use WithModelAddressActions;

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
            $groupId     = $order->group_id;

            data_set($deliveryAddressData, 'group_id', $groupId);

            if (Arr::exists($deliveryAddressData, 'id')) {
                $countryCode = Country::find(Arr::get($deliveryAddressData, 'country_id'))->code;
                data_set($deliveryAddressData, 'country_code', $countryCode);
                $label = isset($deliveryAddressData['label']) ? $deliveryAddressData['label'] : null;
                unset($deliveryAddressData['label']);
                unset($deliveryAddressData['can_edit']);
                unset($deliveryAddressData['can_delete']);
                $updatedAddress     = UpdateAddress::run(Address::find(Arr::get($deliveryAddressData, 'id')), $deliveryAddressData);
                $pivotData['label'] = $label;
                $order->customer->addresses()->updateExistingPivot(
                    $updatedAddress->id,
                    $pivotData
                );
            } else {
                $this->addAddressToModel(
                    $order->customer,
                    $deliveryAddressData,
                    'delivery',
                    false,
                    'delivery_address_id'
                );
            }
        }


        OrderHydrateUniversalSearch::dispatch($order);

        return $order;
    }

    public function rules(): array
    {
        $rules = [
            'reference'        => [
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
            'date'                => ['sometimes', 'required', 'date'],
            'billing_address'     => ['sometimes', 'required', new ValidAddress()],
            'delivery_address'    => ['sometimes', 'required', new ValidAddress()],
            'billing_locked'      => ['sometimes', 'boolean'],
            'delivery_locked'     => ['sometimes', 'boolean'],
            'last_fetched_at'     => ['sometimes', 'date'],
            'payment_amount'      => ['sometimes'],
            'delivery_address_id' => ['sometimes', Rule::exists('addresses', 'id')],
        ];

        if (!$this->strict) {
            $rules['reference'] = ['sometimes', 'string', 'max:64'];
        }

        return $rules;
    }

    public function action(Order $order, array $modelData, bool $strict = true, int $hydratorsDelay = 0, bool $audit = true): Order
    {
        if (!$audit) {
            Order::disableAuditing();
        }
        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->order          = $order;

        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $this->validatedData);
    }

    public function asController(Order $order, ActionRequest $request)
    {
        $this->order = $order;
        $this->initialisationFromShop($order->shop, $request);
        return $this->handle($order, $this->validatedData);
    }
}
