<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order;

use App\Actions\Accounting\Invoice\StoreInvoice;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransaction;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransactionFromAdjustment;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransactionFromCharge;
use App\Actions\Accounting\InvoiceTransaction\StoreInvoiceTransactionFromShipping;
use App\Actions\Ordering\Transaction\StoreTransactionFromAdjustment;
use App\Actions\Ordering\Transaction\StoreTransactionFromCharge;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Accounting\Invoice\InvoiceTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Adjustment;
use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;

class UpdateStateToFinalizedOrder extends OrgAction
{
    use WithActionUpdate;
    use HasOrderHydrators;

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(Order $order): Order
    {
        $billingAddress     = $order->billingAddress;
        $invoiceData = [
            'reference'        => $order->reference,
            'currency_id'      => $order->currency_id,
            'billing_address'  => Arr::except($billingAddress, 'id'),
            'type'             => InvoiceTypeEnum::INVOICE,
            'net_amount'       => $order->net_amount,
            'total_amount'     => $order->total_amount,
            'gross_amount'     => $order->gross_amount,
            'rental_amount'    => 0,
            'goods_amount'     => $order->goods_amount,
            'services_amount'  => $order->services_amount,
            'charges_amount'   => $order->charges_amount,
            'shipping_amount'  => $order->shipping_amount,
            'insurance_amount' => $order->insurance_amount,
            'tax_amount'       => $order->tax_amount
        ];

        $invoice = StoreInvoice::make()->action($order, $invoiceData);

        $transactions = $order->transactions;

        foreach ($transactions as $transaction) {

            $data = [
                'tax_category_id' => $transaction->order->tax_category_id,
                'quantity'        => $transaction->quantity_ordered,
                'gross_amount'    => $transaction->gross_amount,
                'net_amount'      => $transaction->net_amount,
            ];

            if($transaction->model_type == 'Adjustment'){
                $adjustment = Adjustment::find($transaction->model_id);
                StoreInvoiceTransactionFromAdjustment::make()->action($invoice, $adjustment, []);
            } elseif ($transaction->model_type == 'Charge') {
                $chargeData = [
                    'charge_id' => $transaction->model_id
                ];
                StoreInvoiceTransactionFromCharge::make()->action($invoice, $chargeData);
            } elseif ($transaction->model_type == 'ShippingZone') {
                $shippingData = [
                    'shipping_zone_id' => $transaction->model_id
                ];
                StoreInvoiceTransactionFromShipping::make()->action($invoice, $shippingData);
            } else {
                StoreInvoiceTransaction::make()->action($invoice, $transaction->historicAsset, $data);
            }

        }

        $data = [
            'state' => OrderStateEnum::FINALISED
        ];

        if (in_array($order->state, [OrderStateEnum::HANDLING, OrderStateEnum::PACKED])) {
            $order->transactions()->update($data);

            $data[$order->state->value . '_at'] = null;
            $data['packed_at']                  = now();

            $this->update($order, $data);

            $this->orderHydrators($order);

            return $order;
        }

        throw ValidationException::withMessages(['status' => 'You can not change the status to finalized']);
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    public function action(Order $order): Order
    {
        return $this->handle($order);
    }

    public function asController(Order $order, ActionRequest $request)
    {
        $this->order = $order;
        $this->initialisationFromShop($order->shop, $request);
        return $this->handle($order);
    }
}
