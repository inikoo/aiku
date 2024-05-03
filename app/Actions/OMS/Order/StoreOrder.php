<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\OMS\Order;

use App\Actions\Helpers\Address\AttachHistoricAddressToModel;
use App\Actions\Helpers\Address\StoreHistoricAddress;
use App\Actions\Market\Shop\Hydrators\ShopHydrateOrders;
use App\Actions\OMS\Order\Hydrators\OrderHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateOrders;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrders;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Helpers\Address;
use App\Models\Market\Shop;
use App\Models\OMS\Order;
use Illuminate\Http\RedirectResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Redirect;

class StoreOrder extends OrgAction
{
    use AsAction;
    use WithAttributes;

    public int $hydratorsDelay = 0;

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
            $modelData['currency_id'] = $parent->currency_id;
            $modelData['shop_id']     = $parent->id;
        }

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);


        /** @var \App\Models\OMS\Order $order */
        $order = Order::create($modelData);
        $order->stats()->create();

        $billingAddress  = StoreHistoricAddress::run($seedBillingAddress);
        $deliveryAddress = StoreHistoricAddress::run($seedDeliveryAddress);

        AttachHistoricAddressToModel::run($order, $billingAddress, ['scope' => 'billing']);
        AttachHistoricAddressToModel::run($order, $deliveryAddress, ['scope' => 'delivery']);


        HydrateOrder::make()->originalItems($order);

        GroupHydrateOrders::dispatch($order->shop->group)->delay($this->hydratorsDelay);
        OrganisationHydrateOrders::dispatch($order->shop->organisation)->delay($this->hydratorsDelay);
        if (class_basename($parent) == 'Shop') {
            ShopHydrateOrders::dispatch($parent)->delay($this->hydratorsDelay);
        } else {
            ShopHydrateOrders::dispatch($parent->shop)->delay($this->hydratorsDelay);
        }
        OrderHydrateUniversalSearch::dispatch($order);

        return $order->fresh();
    }

    public function rules(): array
    {
        return [
            'number'      => ['required', 'unique:orders', 'numeric'],
            'date'        => ['required'],
            'customer_id' => ['required', 'numeric']
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

        return Redirect::route('grp.org.shops.show.orders.index', $shop);
    }

    public function action(
        Shop|Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress,
    ): Order {
        $this->asAction = true;

        $this->initialisation($parent->organisation, $modelData);

        return $this->handle($parent, $this->validatedData, $seedBillingAddress, $seedDeliveryAddress);
    }

    public function asFetch(
        Shop|Customer|CustomerClient $parent,
        array $modelData,
        Address $seedBillingAddress,
        Address $seedDeliveryAddress,
        int $hydratorsDelay = 60
    ): Order {
        $this->hydratorsDelay = $hydratorsDelay;

        return $this->handle($parent, $modelData, $seedBillingAddress, $seedDeliveryAddress);
    }
}
