<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 28 Nov 2024 21:11:58 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Actions\Traits\WithFixedAddressActions;
use App\Actions\Traits\WithModelAddressActions;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use App\Rules\ValidAddress;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class StoreOrderAddress extends OrgAction
{
    use WithActionUpdate;
    use WithFixedAddressActions;
    use WithModelAddressActions;
    use HasOrderHydrators;

    private Order $order;

    public function handle(Order $order, array $modelData): Order
    {
        $type = Arr::get($modelData, 'type');

        $addressData = $modelData['address']->toArray();
        data_set($addressData, 'group_id', $order->group_id);
        data_set($addressData, 'is_fixed', false);
        data_set($addressData, 'usage', 1);
        data_set($addressData, 'fixed_scope', 'Ordering');

        $addressData = Arr::only($addressData, ['group_id', 'address_line_1','address_line_2', 'sorting_code', 'postal_code', 'dependent_locality', 'locality', 'administrative_area', 'country_code', 'country_id', 'is_fixed', 'fixed_scope', 'usage']);

        $address = Address::create($addressData);


        $order->updateQuietly(
            [
                $type.'_address_id' => $address->id,
                $type.'_country_id'     => $address->country_id,
            ]
        );

        return $order;
    }

    public function rules(): array
    {
        return [

            'address' => ['required', new ValidAddress()],
            'type'    => ['required', Rule::in(['billing', 'delivery'])],

        ];
    }

    public function action(Order $order, array $modelData, int $hydratorsDelay = 0, bool $audit = true): Order
    {
        if (!$audit) {
            Order::disableAuditing();
        }
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->order          = $order;

        $this->initialisationFromShop($order->shop, $modelData);

        return $this->handle($order, $this->validatedData);
    }


}
