<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\Ordering\Order\UpdateOrder;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\ShopifyUserHasFulfilment;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class UpdateOrderWebhooksShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, array $modelData): void
    {
        $shopifyUserHasOrder = ShopifyUserHasFulfilment::where('shopify_user_id', $shopifyUser->id)
            ->where('shopify_order_id', $modelData['id'])
            ->firstOrFail();

        UpdateOrder::run($shopifyUserHasOrder->order, $modelData);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $this->initialisation($shopifyUser->organisation, $request);
        $this->handle($shopifyUser, $this->validatedData);
    }
}
