<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:22 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Shopify\Webhook;

use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Models\Dropshipping\ShopifyUser;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Route;

class StoreWebhooksToShopify extends OrgAction
{
    use AsAction;
    use WithAttributes;
    use WithActionUpdate;

    public string $commandSignature = 'shopify:webhook {shopify}';

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
                "type"  => str_replace('webhooks/shopify-user/{shopifyUser}/', '', $route->uri()),
                "route" => route($route->getName(), [
                    'shopifyUser' => $shopifyUser->id
                ])
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

            if(!$webhook['errors']) {
                $this->update($shopifyUser, [
                    'settings' => [
                        'webhooks' => array_merge($shopifyUser->settings, $webhook['body']['webhook'])
                    ]
                ]);
            }
        }
    }

    public function asController(ShopifyUser $shopifyUser)
    {
        $this->handle($shopifyUser);
    }

    public function asCommand(Command $command)
    {
        $shopifyUser = ShopifyUser::where('name', $command->argument('shopify'))->first();

        $this->handle($shopifyUser);
    }
}
