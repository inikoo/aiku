<?php

/*
 * author Arya Permana - Kirin
 * created on 13-05-2025-10h-24m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Api\Retina\Dropshipping\Order;

use App\Actions\Api\Retina\Dropshipping\Resource\OrderApiResource;
use App\Actions\Ordering\Order\UpdateOrder;
use App\Actions\Ordering\Order\UpdateOrderExtraPacking;
use App\Actions\Ordering\Order\UpdateOrderGiftMessage;
use App\Actions\Ordering\Order\UpdateOrderInsurance;
use App\Actions\Ordering\Order\UpdateOrderPremiumDispatch;
use App\Actions\RetinaApiAction;
use App\Enums\Catalogue\Charge\ChargeStateEnum;
use App\Enums\Catalogue\Charge\ChargeTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Ordering\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateApiOrder extends RetinaApiAction
{
    use AsAction;
    use WithAttributes;

    public function handle(Order $order, array $modelData): Order|JsonResponse
    {
        if ($order->customer_id != $this->customer->id || $order->shop_id != $this->shop->id) {
            return response()->json([
                'message' => "Unable to make modifications for this order"
            ], 403);
        }

        if ($order->state != OrderStateEnum::CREATING) {
            return response()->json([
                'message' => "This order is already in the '{$order->state->value}' state and cannot be updated."
            ], 409);
        }

        $checkoutOptions = [
            'is_premium_dispatch' => [ChargeTypeEnum::PREMIUM, UpdateOrderPremiumDispatch::class],
            'has_extra_packing'   => [ChargeTypeEnum::PACKING, UpdateOrderExtraPacking::class],
            'has_insurance'       => [ChargeTypeEnum::INSURANCE, UpdateOrderInsurance::class],
            'has_gift_message'    => [ChargeTypeEnum::GIFT_MESSAGE, UpdateOrderGiftMessage::class],
        ];

        $requestedOptions = Arr::only($modelData, array_keys($checkoutOptions));

        $optionsToCheck = $requestedOptions;
        if (filled(Arr::get($modelData, 'gift_message'))) {
            $optionsToCheck['has_gift_message'] = true;
        }

        foreach ($optionsToCheck as $option => $isRequested) {
            [$chargeType] = $checkoutOptions[$option];
            if ($isRequested && !$order->shop->charges()->where('type', $chargeType)->where('state', ChargeStateEnum::ACTIVE)->exists()) {
                return response()->json([
                    'message' => "The option '$option' is not available in this shop."
                ], 422);
            }
        }

        foreach ($requestedOptions as $option => $isRequested) {
            [, $updateOptionAction] = $checkoutOptions[$option];
            $updateOptionAction::make()->action($order, [$option => (bool)$isRequested]);
        }

        return UpdateOrder::make()->action($order->refresh(), Arr::except($modelData, array_keys($checkoutOptions)));
    }

    public function rules(): array
    {
        return [
            'public_notes'        => ['sometimes', 'nullable', 'string', 'max:4000'],
            'customer_notes'      => ['sometimes', 'nullable', 'string', 'max:4000'],
            'shipping_notes'      => ['sometimes', 'nullable', 'string', 'max:4000'],
            'gift_message'        => ['sometimes', 'nullable', 'string', 'max:500'],
            'is_premium_dispatch' => ['sometimes', 'boolean'],
            'has_extra_packing'   => ['sometimes', 'boolean'],
            'has_insurance'       => ['sometimes', 'boolean'],
            'has_gift_message'    => ['sometimes', 'boolean'],
        ];
    }

    public function jsonResponse(Order|JsonResponse $result)
    {
        if ($result instanceof JsonResponse) {
            return $result;
        }

        return OrderApiResource::make($result)
            ->additional([
                'message' => __('Order updated successfully'),
            ]);
    }

    public function asController(Order $order, ActionRequest $request): Order|JsonResponse
    {
        $this->initialisationFromDropshipping($request);

        return $this->handle($order, $this->validatedData);
    }
}
