<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 14:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Ordering\Order\UpdateState\SendOrderToWarehouse;
use App\Actions\OrgAction;
use App\Actions\Retina\Accounting\Payment\WithPlaceOrderByPaymentMethod;
use App\Actions\Traits\Authorisations\Ordering\WithOrderingEditAuthorisation;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\PaymentAccount\PaymentAccountTypeEnum;
use App\Enums\Accounting\PaymentAccountShop\PaymentAccountShopStateEnum;
use App\Enums\Ordering\Order\OrderPayStatusEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Order\OrderToBePaidByEnum;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Ordering\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

/**
 * A customer who placed an order to be paid by bank transfer and then asks to pay the courier
 * instead (HELP-3913). The order ends as if it had been placed with cash on delivery at checkout:
 * the charge is on it, and it goes to the warehouse without waiting for a payment.
 */
class ChangeOrderToCashOnDelivery extends OrgAction
{
    use WithActionUpdate;
    use WithOrderingEditAuthorisation;
    use WithPlaceOrderByPaymentMethod;

    private Order $order;

    /**
     * @throws \Throwable
     */
    public function handle(Order $order): Order
    {
        $order = DB::transaction(function () use ($order) {
            $order = $this->addCashOnDeliveryCharges($order);

            return $this->update($order, ['to_be_paid_by' => OrderToBePaidByEnum::CASH_ON_DELIVERY]);
        });

        SendOrderToWarehouse::make()->action($order, []);

        return $order->refresh();
    }

    public function blockingReason(Order $order): ?string
    {
        if ($order->state != OrderStateEnum::SUBMITTED) {
            return __('Only a submitted order can be changed to cash on delivery.');
        }

        if ($order->pay_status != OrderPayStatusEnum::UNPAID) {
            return __('Only an unpaid order can be changed to cash on delivery.');
        }

        if ($order->to_be_paid_by == OrderToBePaidByEnum::CASH_ON_DELIVERY) {
            return __('This order is already cash on delivery.');
        }

        if ($order->preOrder) {
            return __('A pre-order cannot be paid by cash on delivery.');
        }

        /** @var PaymentAccountShop|null $cashOnDelivery */
        $cashOnDelivery = $order->shop->paymentAccountShops()
            ->where('state', PaymentAccountShopStateEnum::ACTIVE)
            ->where('show_in_checkout', true)
            ->where('type', PaymentAccountTypeEnum::CASH_ON_DELIVERY)
            ->first();

        if (!$cashOnDelivery) {
            return __('This shop does not offer cash on delivery.');
        }

        if (!in_array($order->deliveryAddress?->country_id, Arr::get($cashOnDelivery->paymentAccount->data, 'countries', []))) {
            return __('Cash on delivery is not offered in the delivery country of this order.');
        }

        return null;
    }

    public function afterValidator(Validator $validator): void
    {
        $blockingReason = $this->blockingReason($this->order);
        if ($blockingReason) {
            $validator->errors()->add('to_be_paid_by', $blockingReason);
        }
    }

    /**
     * @throws \Throwable
     */
    public function action(Order $order): Order
    {
        $this->asAction = true;
        $this->order    = $order;
        $this->initialisationFromShop($order->shop, []);

        return $this->handle($order);
    }

    /**
     * @throws \Throwable
     */
    public function asController(Order $order, ActionRequest $request): Order
    {
        $this->order = $order;
        $this->initialisationFromShop($order->shop, $request);

        return $this->handle($order);
    }

    public function htmlResponse(): RedirectResponse
    {
        return back();
    }
}
