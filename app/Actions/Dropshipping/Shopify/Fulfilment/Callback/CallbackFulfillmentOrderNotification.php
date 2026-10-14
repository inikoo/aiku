<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 18 Feb 2025 10:56:59 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Fulfilment\Callback;

use App\Actions\WebhookAction;
use App\Models\Dropshipping\ShopifyUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\ActionRequest;

class CallbackFulfillmentOrderNotification extends WebhookAction
{
    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        [$status, $message] = match (Arr::get($modelData, 'kind')) {
            'FULFILLMENT_REQUEST' => RetrieveShopifyAssignedOrders::run($shopifyUser),
            'CANCELLATION_REQUEST' => RetrieveShopifyCancelledOrders::run($shopifyUser),
            default => [true, ''],
        };

        if (!$status) {
            Log::warning('Shopify order pickup failed for '.$shopifyUser->customerSalesChannel?->slug.' ('.$shopifyUser->name.'): '.$message);
        }
    }


    public function rules(): array
    {
        return [
            'kind' => ['required', 'string'],
        ];
    }

    /**
     * @throws \Throwable
     */
    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        if (!$shopifyUser->customer_id) {
            abort(422);
        }

        $this->initialisation($request);

        $this->handle($shopifyUser, $this->validatedData);
    }
}
