<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Thu, 11 Jul 2024 10:16:14 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\CRM\Customer;
use App\Models\Dropshipping\ShopifyUser;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Route;

class StoreWebhooksToShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    /**
     * @throws \Exception
     */
    public function handle(ShopifyUser $shopifyUser)
    {
        $webhooks     = [];
        $webhookTypes = [];
        $routes       = collect(Route::getRoutes())->filter(function ($route) {
            return str_contains($route->getName(), 'webhooks.shopify');
        });

        foreach ($routes as $route) {
            $webhookTypes[] =             [
                "type"  => str_replace('webhooks/shopify/', '', $route->uri()),
                "route" => route($route->getName())
            ];
        }

        foreach ($webhookTypes as $webhookType) {
            $webhooks[]      = [
                "webhook" => [
                    "topic"   => $webhookType["type"],
                    "address" => $webhookType["route"],
                    "format"  => "json"
                ]
            ];
        }

        foreach ($webhooks as $webhook) {
            $webhook = $shopifyUser->api()->getRestClient()->request('POST', 'admin/api/2024-07/webhooks.json', $webhook);

            $this->update($shopifyUser, [
                'data' => [
                    'webhooks' => $webhook['body']
                ]
            ]);
        }
    }

    public function asController(Customer $customer, ShopifyUser $shopifyUser)
    {
        $this->handle($shopifyUser);
    }
}
