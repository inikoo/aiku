<?php

namespace App\Listeners;

use App\Actions\Dropshipping\Shopify\Webhook\StoreWebhooksToShopify;
use App\Models\Dropshipping\ShopifyUser;
use Arr;
use Osiset\ShopifyApp\Messaging\Events\ShopAuthenticatedEvent;

class ShopifyAuthenticatedShopWebhookRegister
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ShopAuthenticatedEvent $event): void
    {
        $shopifyUser = ShopifyUser::find($event->shopId->toNative());

        if(!Arr::exists($shopifyUser->data, 'webhooks')) {
            StoreWebhooksToShopify::run($shopifyUser);
        }
    }
}
