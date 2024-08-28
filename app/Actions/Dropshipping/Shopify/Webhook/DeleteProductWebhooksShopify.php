<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\Dropshipping\Shopify\Product\DeleteProductFromShopify;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteProductWebhooksShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public function handle(ShopifyUser $shopifyUser, int $productId): void
    {
        $product = $shopifyUser->products()
            ->where("shopify_product_id", $productId)
            ->firstOrFail();

        DeleteProductFromShopify::run($product);
    }

    public function asController(ShopifyUser $shopifyUser, ActionRequest $request): void
    {
        $productId = $request->input("id");

        $this->handle($shopifyUser, $productId);
    }
}
