<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 25-06-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Api\Retina\Dropshipping\Resource;

use App\Actions\Retina\Ecom\Basket\UI\IsOrder;
use App\Http\Resources\CRM\CustomerResource;
use App\Http\Resources\HasSelfCall;
use App\Http\Resources\Helpers\AddressResource;
use App\Models\Helpers\Address;
use App\Models\Ordering\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderApiResource extends JsonResource
{
    use IsOrder;
    use HasSelfCall;

    public function toArray($request): array
    {
        /** @var Order $order */
        $order = $this->resource;
        $payAmount   = $order->total_amount - $order->payment_amount;
        $roundedDiff = round($payAmount, 2);

        $estWeight = ($order->estimated_weight ?? 0) / 1000;

        $shippingData = is_array($order->shipping_data ?? []) ? $order->shipping_data : [];

        $shipments = array_is_list($shippingData) ? $shippingData : [];

        $firstShippingData = $shipments[0] ?? [];

        return [
            'id'            => $order->id,
            'reference'     => $order->reference,
            'state'         => $order->state,
            'pay_status'    => $order->pay_status,
            'net_amount'    => $order->net_amount,
            'total_amount'    => $order->total_amount,
            'item_amount' => $order->goods_amount,
            'item_quantity' => $order->stats->number_item_transactions,
            'tax_amount' => $order->tax_amount,
            'shipping_amount' => $order->shipping_amount,
            'charges_amount' => $order->charges_amount,
            'date'          => $order->date,
            'is_premium_dispatch' => $order->is_premium_dispatch,
            'has_extra_packing' => $order->has_extra_packing,
            'has_insurance' => $order->has_insurance,
            'has_gift_message' => $order->has_gift_message,
            'gift_message' => $order->gift_message,
            'public_notes' => $order->public_notes,
            'customer_notes' => $order->customer_notes,
            'shipping_notes' => $order->shipping_notes,
            'customer' => CustomerResource::make($order->customer)->getArray(),
            'customer_client' => $order->customerClient ? CustomerClientApiResource::make($order->customerClient)->getArray() : null,
            'delivery_address' => AddressResource::make($order->deliveryAddress ?? new Address()),
            'billing_address' => AddressResource::make($order->billingAddress ?? new Address()),
            'products' => [
                'payment'          => [
                    'total_amount' => (float)$order->total_amount,
                    'paid_amount'  => (float)$order->payment_amount,
                    'pay_amount'   => $roundedDiff,
                ],
                'estimated_weight' => $estWeight
            ],
            'shipping_data' => [
                'shipping_id'     => data_get($firstShippingData, 'shipping_id'),
                'trackings'       => data_get($firstShippingData, 'trackings'),
                'tracking_urls'   => data_get($firstShippingData, 'tracking_urls'),
                'shipper_label'   => data_get($firstShippingData, 'shipper_label'),
            ],
            'shipments' => array_map(fn (array $shipment) => [
                'delivery_note_reference' => data_get($shipment, 'delivery_note_reference'),
                'shipper_label'           => data_get($shipment, 'shipper_label'),
                'tracking_number'         => data_get($shipment, 'tracking_number'),
                'trackings'               => data_get($shipment, 'trackings'),
                'tracking_urls'           => data_get($shipment, 'tracking_urls'),
            ], $shipments),
            'transactions' => TransactionsApiResource::collection($order->transactions),
            'payments' => PaymentsApiResource::collection($order->payments),
        ];
    }
}
