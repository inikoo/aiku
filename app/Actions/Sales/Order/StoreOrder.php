<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Nov 2021 16:15:50 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Sales\Order;

use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Marketing\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\Sales\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateOrders;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Redirect;

class StoreOrder
{
    use AsAction;
    use WithAttributes;

    public int $hydratorsDelay=0;

    public function handle(
        Shop|Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress
    ): Order {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
            $modelData['currency_id'] = $parent->shop->currency_id;
            $modelData['shop_id']     = $parent->shop_id;
        } elseif (class_basename($parent) == 'CustomerClient') {
            $modelData['customer_id']        = $parent->customer_id;
            $modelData['customer_client_id'] = $parent->id;
            $modelData['currency_id']        = $parent->shop->currency_id;
            $modelData['shop_id']            = $parent->shop_id;
        } else {
            $modelData['customer_id'] = $parent->customer_id;
            $modelData['currency_id'] = $parent->currency_id;
            $modelData['shop_id']     = $parent->id;
        }

        /** @var Order $order */
        $order = $parent->shop->orders()->create($modelData);
        $order->stats()->create();

        $billingAddress  = StoreHistoricAddress::run($seedBillingAddress);
        $deliveryAddress = StoreHistoricAddress::run($seedDeliveryAddress);

        AttachHistoricAddressToModel::run($order, $billingAddress, ['scope'=>'billing']);
        AttachHistoricAddressToModel::run($order, $deliveryAddress, ['scope'=>'delivery']);


        HydrateOrder::make()->originalItems($order);

        TenantHydrateOrders::dispatch(app('currentTenant'))->delay($this->hydratorsDelay);
        ShopHydrateOrders::dispatch($parent->shop)->delay($this->hydratorsDelay);

        OrderHydrateUniversalSearch::dispatch($order);

        return $order->fresh();
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'unique:tenant.orders', 'numeric'],
            'date'   => ['required']
        ];
    }

    public function inShop(Shop $shop, ActionRequest $request): RedirectResponse
    {
        $request->validate();
        $seedBillingAddress = new Address();
        $seedBillingAddress::hydrate($request->get('billing_address'));
        $seedDeliveryAddress = new Address();
        $seedBillingAddress::hydrate($request->get('delivery_address'));
        $this->handle($shop, $request->validated(), $seedBillingAddress, $seedDeliveryAddress);
        return  Redirect::route('shops.show.orders.index', $shop);
    }

    public function action(
        Shop|Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress
    ): Order {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData, $seedBillingAddress, $seedDeliveryAddress);
    }

    public function asFetch(
        Shop|Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress,
        int $hydratorsDelay=60
    ): Order {
        $this->hydratorsDelay=$hydratorsDelay;
        return $this->handle($parent, $modelData, $seedBillingAddress, $seedDeliveryAddress);
    }
}
