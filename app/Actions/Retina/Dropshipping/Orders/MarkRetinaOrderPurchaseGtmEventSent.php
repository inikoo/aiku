<?php

/*
 * Author: Vika Aqordi <aqordivika@yahoo.co.id>
 * Created: Tue, 13 Oct 2026, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Dropshipping\Orders;

use App\Actions\RetinaAction;
use App\Models\Ordering\Order;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;

class MarkRetinaOrderPurchaseGtmEventSent extends RetinaAction
{
    public function handle(Order $order): Order
    {
        if ($order->submitted_at && !data_get($order->data, 'gtm_purchase_sent_at')) {
            $order->updateQuietly([
                'data->gtm_purchase_sent_at' => now()->toIso8601String()
            ]);
        }

        return $order;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->route('order')->customer_id == $this->customer->id;
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json(['sent' => true]);
    }

    public function asController(Order $order, ActionRequest $request): Order
    {
        $this->initialisation($request);

        return $this->handle($order);
    }
}
