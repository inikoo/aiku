<?php

namespace App\Listeners;

use App\Models\Dropshipping\ShopifyUser;
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
    }
}
