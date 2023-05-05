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
use App\Models\Sales\Customer;
use App\Models\Sales\Order;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreOrder
{
    use AsAction;
    use WithAttributes;

    public function handle(
        Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress
    ): Order {
        if (class_basename($parent) == 'Customer') {
            $modelData['customer_id'] = $parent->id;
        } else {
            $modelData['customer_id']        = $parent->customer_id;
            $modelData['customer_client_id'] = $parent->id;
        }

        $modelData['currency_id'] = $parent->shop->currency_id;
        $modelData['shop_id']     = $parent->shop_id;


        /** @var Order $order */
        $order = $parent->shop->orders()->create($modelData);
        $order->stats()->create();

        $billingAddress  = StoreHistoricAddress::run($seedBillingAddress);
        $deliveryAddress = StoreHistoricAddress::run($seedDeliveryAddress);

        AttachHistoricAddressToModel::run($order, $billingAddress, ['scope'=>'billing']);
        AttachHistoricAddressToModel::run($order, $deliveryAddress, ['scope'=>'delivery']);

        HydrateOrder::make()->originalItems($order);

        TenantHydrateOrders::run(app('currentTenant'));
        ShopHydrateOrders::run($parent->shop);

        OrderHydrateUniversalSearch::dispatch($order);

        return $order;
    }

    public function rules(): array
    {
        return [
            'number' => ['required', 'unique:tenant.orders'],
            'date'   => ['required', 'date']
        ];
    }

    public function action(
        Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress
    ): Order {
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData, $seedBillingAddress, $seedDeliveryAddress);
    }
}
